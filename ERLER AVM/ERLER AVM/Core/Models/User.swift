import Foundation

struct User: Codable {
    let id: Int
    let name: String
    let email: String
    let isAdmin: Bool
    
    enum CodingKeys: String, CodingKey {
        case id
        case name
        case email
        case isAdmin = "is_admin"
    }
}

struct AuthResponse: Codable {
    let user: User
    let token: String
}
