import Foundation

struct HomeResponse: Codable {
    let banners: [String: [Banner]]
    let categories: [Category]
    let brands: [Brand]
    let campaigns: [Campaign]
    let featuredProducts: [Product]
    
    enum CodingKeys: String, CodingKey {
        case banners, categories, brands, campaigns
        case featuredProducts = "featured_products"
    }
}
