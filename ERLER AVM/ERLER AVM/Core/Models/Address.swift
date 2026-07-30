import Foundation

struct CustomerAddress: Codable, Identifiable, Equatable {
    var id: String { publicId }

    let publicId: String
    let type: String
    let title: String
    let fullName: String
    let phone: String
    let city: String
    let district: String
    let postalCode: String?
    let addressLine: String
    let isDefault: Bool

    enum CodingKeys: String, CodingKey {
        case publicId = "public_id"
        case type, title, phone, city, district
        case fullName = "full_name"
        case postalCode = "postal_code"
        case addressLine = "address_line"
        case isDefault = "is_default"
    }
}

struct CustomerAddressRequest: Encodable {
    let type: String
    let title: String
    let fullName: String
    let phone: String
    let city: String
    let district: String
    let postalCode: String?
    let addressLine: String
    let isDefault: Bool

    enum CodingKeys: String, CodingKey {
        case type, title, phone, city, district
        case fullName = "full_name"
        case postalCode = "postal_code"
        case addressLine = "address_line"
        case isDefault = "is_default"
    }
}
