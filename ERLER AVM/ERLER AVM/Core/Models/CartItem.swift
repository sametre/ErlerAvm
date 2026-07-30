import Foundation

struct CartResponse: Codable {
    let items: [CartItem]
    let subtotal: String
    let count: Int
}

struct CartItem: Codable, Identifiable {
    var id: String { publicId }
    let publicId: String
    let name: String
    let price: String
    let quantity: Int
    let lineTotal: String
    let stockQuantity: Int
    let image: String?
    
    enum CodingKeys: String, CodingKey {
        case publicId = "public_id"
        case name, price, quantity
        case lineTotal = "line_total"
        case stockQuantity = "stock_quantity"
        case image
    }
}
