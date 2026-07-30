import Foundation
import OSLog

private let apiLogger = Logger(subsystem: "com.erleravm.mobile", category: "API")

final class APIClient {
    static let shared = APIClient()

    private let baseURL = APIEnvironment.apiBaseURL
    private let session: URLSession
    
    private init() {
        let config = URLSessionConfiguration.default
        config.timeoutIntervalForRequest = 30
        config.timeoutIntervalForResource = 300
        #if DEBUG
        self.session = URLSession(configuration: config, delegate: LocalDevelopmentURLSessionDelegate(), delegateQueue: nil)
        #else
        self.session = URLSession(configuration: config)
        #endif
    }
    
    func request<T: Codable>(_ endpoint: APIEndpoint, useCache: Bool = true) async throws -> T {
        guard let url = endpoint.url(base: baseURL) else {
            throw APIError.invalidURL
        }
        
        let cacheKey = self.cacheKey(for: url)
        
        // 1. Return cached data immediately if allowed (for GET requests)
        var request = URLRequest(url: url)
        request.httpMethod = endpoint.method.rawValue
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        request.setValue("XMLHttpRequest", forHTTPHeaderField: "X-Requested-With")
        applyLocalDevelopmentHeaders(to: &request)
        
        if let body = endpoint.body {
            request.setValue("application/json", forHTTPHeaderField: "Content-Type")
            let encoder = JSONEncoder()
            request.httpBody = try encoder.encode(AnyEncodable(body))
        }
        
        if let token = TokenManager.shared.getToken() {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }
        
        do {
            let (data, response) = try await session.data(for: request)
            
            guard let httpResponse = response as? HTTPURLResponse else {
                throw APIError.invalidResponse
            }
            
            if httpResponse.statusCode == 401 {
                _ = TokenManager.shared.deleteToken()
                NotificationCenter.default.post(name: Notification.Name("UserDidLogout"), object: nil)
                throw APIError.unauthenticated
            }
            
            let decoder = JSONDecoder()
            let apiResponse = try decoder.decode(APIResponse<T>.self, from: data)
            
            if apiResponse.success == false || (400...599).contains(httpResponse.statusCode) {
                throw APIError.serverError(message: apiResponse.message ?? apiResponse.validationMessage ?? "Bir hata oluştu.")
            }
            
            guard let responseData = apiResponse.data else {
                if T.self == EmptyResponse.self {
                    return EmptyResponse() as! T
                }
                throw APIError.decodingError
            }
            
            // Save successful GET response to cache
            if useCache && endpoint.method == .get {
                CacheManager.shared.save(data: apiResponse, for: cacheKey)
            }
            
            return responseData
            
        } catch let error as APIError {
            // Fallback to cache on network failure
            if useCache && endpoint.method == .get, let cachedData: APIResponse<T> = CacheManager.shared.load(for: cacheKey, as: APIResponse<T>.self), let data = cachedData.data {
                return data
            }
            throw error
        } catch let error as DecodingError {
            apiLogger.error("API response decoding failed: \(String(describing: error), privacy: .public)")
            throw APIError.decodingError
        } catch {
            // Fallback to cache on network failure
            if useCache && endpoint.method == .get, let cachedData: APIResponse<T> = CacheManager.shared.load(for: cacheKey, as: APIResponse<T>.self), let data = cachedData.data {
                return data
            }
            throw APIError.networkError(error)
        }
    }

    func requestPage<T: Codable>(_ endpoint: APIEndpoint, useCache: Bool = true) async throws -> PaginatedList<T> {
        guard let url = endpoint.url(base: baseURL) else {
            throw APIError.invalidURL
        }

        let cacheKey = self.cacheKey(for: url)

        var request = URLRequest(url: url)
        request.httpMethod = endpoint.method.rawValue
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        request.setValue("XMLHttpRequest", forHTTPHeaderField: "X-Requested-With")
        applyLocalDevelopmentHeaders(to: &request)

        if let token = TokenManager.shared.getToken() {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }

        do {
            let (data, response) = try await session.data(for: request)

            guard let httpResponse = response as? HTTPURLResponse else {
                throw APIError.invalidResponse
            }

            if httpResponse.statusCode == 401 {
                _ = TokenManager.shared.deleteToken()
                NotificationCenter.default.post(name: Notification.Name("UserDidLogout"), object: nil)
                throw APIError.unauthenticated
            }

            let decoder = JSONDecoder()
            let apiResponse = try decoder.decode(APIResponse<[T]>.self, from: data)

            if apiResponse.success == false || (400...599).contains(httpResponse.statusCode) {
                throw APIError.serverError(message: apiResponse.message ?? apiResponse.validationMessage ?? "Bir hata oluştu.")
            }

            let page = PaginatedList(data: apiResponse.data ?? [], meta: apiResponse.meta)

            if useCache && endpoint.method == .get {
                CacheManager.shared.save(data: page, for: cacheKey)
            }

            return page
        } catch let error as APIError {
            if useCache && endpoint.method == .get, let cached: PaginatedList<T> = CacheManager.shared.load(for: cacheKey, as: PaginatedList<T>.self) {
                return cached
            }
            throw error
        } catch let error as DecodingError {
            apiLogger.error("Paginated API response decoding failed: \(String(describing: error), privacy: .public)")
            throw APIError.decodingError
        } catch {
            if useCache && endpoint.method == .get, let cached: PaginatedList<T> = CacheManager.shared.load(for: cacheKey, as: PaginatedList<T>.self) {
                return cached
            }
            throw APIError.networkError(error)
        }
    }

    private func cacheKey(for url: URL) -> String {
        "\(TokenManager.shared.cacheNamespace):\(url.absoluteString)"
    }

    private func applyLocalDevelopmentHeaders(to request: inout URLRequest) {
        #if DEBUG
        if let host = APIEnvironment.localDevelopmentHostHeader {
            request.setValue(host, forHTTPHeaderField: "Host")
        }
        #endif
    }
}

#if DEBUG
private final class LocalDevelopmentURLSessionDelegate: NSObject, URLSessionDelegate {
    private let trustedLocalHosts = Set(["127.0.0.1", "localhost", "erleravm.test"])

    nonisolated func urlSession(
        _ session: URLSession,
        didReceive challenge: URLAuthenticationChallenge,
        completionHandler: @escaping (URLSession.AuthChallengeDisposition, URLCredential?) -> Void
    ) {
        guard
            challenge.protectionSpace.authenticationMethod == NSURLAuthenticationMethodServerTrust,
            trustedLocalHosts.contains(challenge.protectionSpace.host),
            let serverTrust = challenge.protectionSpace.serverTrust
        else {
            completionHandler(.performDefaultHandling, nil)
            return
        }

        completionHandler(.useCredential, URLCredential(trust: serverTrust))
    }
}
#endif

private struct AnyEncodable: Encodable {
    private let encodeValue: (Encoder) throws -> Void

    init(_ wrapped: Encodable) {
        self.encodeValue = wrapped.encode
    }

    func encode(to encoder: Encoder) throws {
        try encodeValue(encoder)
    }
}

private extension APIResponse {
    var validationMessage: String? {
        errors?.values.first?.first
    }
}
