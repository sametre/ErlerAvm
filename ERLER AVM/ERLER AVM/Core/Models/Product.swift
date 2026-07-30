import Foundation

struct Product: Codable, Identifiable {
    let id: String
    let name: String
    let slug: String
    let price: String
    let compareAtPrice: String?
    let stockQuantity: Int
    let images: [String]
    let brand: Brand?
    let category: Category?
    let shortDescription: String?
    let description: String?
    let reviews: [Review]?
    let averageRating: Double?
    let reviewCount: Int?
    
    // Virtual ID for SwiftUI
    var uniqueId: String { id }
    
    enum CodingKeys: String, CodingKey {
        case id = "public_id"
        case name, slug, price, images, brand, category
        case compareAtPrice = "compare_at_price"
        case stockQuantity = "stock_quantity"
        case shortDescription = "short_description"
        case description
        case reviews
        case averageRating = "average_rating"
        case reviewCount = "review_count"
    }
}

struct Review: Codable, Identifiable {
    let id: Int
    let rating: Int
    let comment: String
    let userName: String
    let createdAt: String
    
    enum CodingKeys: String, CodingKey {
        case id, rating, comment
        case userName = "user_name"
        case createdAt = "created_at"
    }
}
