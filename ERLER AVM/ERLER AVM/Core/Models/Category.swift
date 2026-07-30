import Foundation

struct Category: Codable, Identifiable {
    let id: String
    let name: String
    let slug: String
    let description: String?
    let image: String?
    let children: [Category]?
    
    enum CodingKeys: String, CodingKey {
        case id = "public_id"
        case name, slug, description, image, children
    }
}

struct Brand: Codable, Identifiable {
    let id: String
    let name: String
    let slug: String
    let description: String?
    
    enum CodingKeys: String, CodingKey {
        case id = "public_id"
        case name, slug, description
    }
}

struct Campaign: Codable, Identifiable {
    let id: String
    let name: String
    let slug: String
    let description: String?
    let startsAt: String?
    let endsAt: String?
    
    enum CodingKeys: String, CodingKey {
        case id = "public_id"
        case name, slug, description
        case startsAt = "starts_at"
        case endsAt = "ends_at"
    }
}

struct Banner: Codable, Identifiable {
    var id: String { imagePath }
    let placement: String
    let title: String?
    let subtitle: String?
    let ctaLabel: String?
    let ctaUrl: String?
    let imagePath: String
    
    enum CodingKeys: String, CodingKey {
        case placement, title, subtitle
        case ctaLabel = "cta_label"
        case ctaUrl = "cta_url"
        case imagePath = "image_path"
    }
}
