import Foundation

struct OrderShort: Codable, Identifiable {
    var id: String { publicId }

    let publicId: String
    let orderNumber: String
    let status: String?
    let statusLabel: String?
    let paymentStatus: String?
    let paymentStatusLabel: String?
    let grandTotal: String
    let currency: String?
    let paymentRequired: Bool?
    let paymentEndpoint: String?
    let createdAt: String?

    var displayPaymentStatus: String {
        paymentStatusLabel ?? paymentStatus ?? "Bekliyor"
    }

    var displayStatus: String {
        statusLabel ?? status ?? "Sipariş alındı"
    }

    enum CodingKeys: String, CodingKey {
        case publicId = "public_id"
        case orderNumber = "order_number"
        case status
        case statusLabel = "status_label"
        case paymentStatus = "payment_status"
        case paymentStatusLabel = "payment_status_label"
        case grandTotal = "grand_total"
        case currency
        case paymentRequired = "payment_required"
        case paymentEndpoint = "payment_endpoint"
        case createdAt = "created_at"
    }
}

struct OrderDetail: Codable, Identifiable {
    var id: String { publicId }

    let publicId: String
    let orderNumber: String
    let status: String
    let statusLabel: String?
    let paymentStatus: String
    let paymentStatusLabel: String?
    let subtotal: String
    let shippingTotal: String
    let discountTotal: String
    let grandTotal: String
    let currency: String
    let paymentRequired: Bool?
    let paymentEndpoint: String?
    let createdAt: String
    let items: [OrderItem]

    var displayPaymentStatus: String {
        paymentStatusLabel ?? paymentStatus
    }

    var displayStatus: String {
        statusLabel ?? status
    }

    enum CodingKeys: String, CodingKey {
        case publicId = "public_id"
        case orderNumber = "order_number"
        case status
        case statusLabel = "status_label"
        case paymentStatus = "payment_status"
        case paymentStatusLabel = "payment_status_label"
        case subtotal
        case shippingTotal = "shipping_total"
        case discountTotal = "discount_total"
        case grandTotal = "grand_total"
        case currency
        case paymentRequired = "payment_required"
        case paymentEndpoint = "payment_endpoint"
        case createdAt = "created_at"
        case items
    }
}

struct OrderItem: Codable, Identifiable {
    let publicId: String
    let name: String
    let sku: String?
    let quantity: Int
    let unitPrice: String
    let lineTotal: String
    let productPublicId: String?
    let productUrl: String?

    var id: String { publicId }

    enum CodingKeys: String, CodingKey {
        case publicId = "public_id"
        case name
        case sku
        case quantity
        case unitPrice = "unit_price"
        case lineTotal = "line_total"
        case productPublicId = "product_public_id"
        case productUrl = "product_url"
    }
}

struct OrderResponse: Codable {
    let order: OrderShort
}

struct PaymentStartResponse: Codable {
    let paymentRequired: Bool
    let paymentStatus: String?
    let provider: String?
    let checkoutURL: String?

    enum CodingKeys: String, CodingKey {
        case paymentRequired = "payment_required"
        case paymentStatus = "payment_status"
        case provider
        case checkoutURL = "checkout_url"
    }
}
