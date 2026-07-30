import Foundation

enum APIEnvironment {
    static var apiBaseURL: String {
        normalizedInfoValue("API_BASE_URL", fallback: defaultApiBaseURL)
    }

    static var webBaseURL: String {
        normalizedInfoValue("WEB_BASE_URL", fallback: defaultWebBaseURL)
    }

    static var localDevelopmentHostHeader: String? {
        #if DEBUG
        let apiHost = URL(string: apiBaseURL)?.host

        if apiHost == "127.0.0.1" || apiHost == "localhost" {
            return "erleravm.test"
        }
        #endif

        return nil
    }

    static func assetURL(_ path: String?) -> URL? {
        guard let path, !path.isEmpty else { return nil }

        if path.hasPrefix("http://") || path.hasPrefix("https://") {
            return URL(string: path)
        }

        let normalizedPath = path.hasPrefix("/") ? path : "/\(path)"
        return URL(string: webBaseURL + normalizedPath)
    }

    private static var defaultApiBaseURL: String {
        #if DEBUG
        "https://127.0.0.1/api/v1"
        #else
        "https://api.erleravm.com.tr/api/v1"
        #endif
    }

    private static var defaultWebBaseURL: String {
        #if DEBUG
        "https://erleravm.test"
        #else
        "https://erleravm.com.tr"
        #endif
    }

    private static func normalizedInfoValue(_ key: String, fallback: String) -> String {
        let value = (Bundle.main.object(forInfoDictionaryKey: key) as? String)?
            .trimmingCharacters(in: .whitespacesAndNewlines)

        guard let value, !value.isEmpty, !value.hasPrefix("$(") else {
            return fallback.trimmingCharacters(in: CharacterSet(charactersIn: "/"))
        }

        return value.trimmingCharacters(in: CharacterSet(charactersIn: "/"))
    }
}
