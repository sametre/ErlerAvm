import Foundation

struct APIResponse<T: Codable>: Codable {
    let success: Bool?
    let message: String?
    let data: T?
    let errors: [String: [String]]?
    let meta: APIMeta?
}

struct APIMeta: Codable {
    let currentPage: Int
    let lastPage: Int
    let perPage: Int
    let total: Int
    
    enum CodingKeys: String, CodingKey {
        case currentPage = "current_page"
        case lastPage = "last_page"
        case perPage = "per_page"
        case total
    }
}

// Helper generic types for common empty responses
struct EmptyResponse: Codable {}
