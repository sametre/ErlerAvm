import Foundation

enum HTTPMethod: String {
    case get = "GET"
    case post = "POST"
    case put = "PUT"
    case patch = "PATCH"
    case delete = "DELETE"
}

struct APIEndpoint {
    var path: String
    var method: HTTPMethod
    var body: Encodable?
    var queryItems: [URLQueryItem]?
    
    func url(base: String) -> URL? {
        var components = URLComponents(string: base + path)
        if let queryItems = queryItems {
            components?.queryItems = queryItems
        }
        return components?.url
    }
}

// MARK: - Pre-defined Endpoints
extension APIEndpoint {
    static let home = APIEndpoint(path: "/home", method: .get)
    static let categories = APIEndpoint(path: "/categories", method: .get)
    static func category(slug: String) -> APIEndpoint { APIEndpoint(path: "/categories/\(slug)", method: .get) }
    
    static func product(id: String) -> APIEndpoint { APIEndpoint(path: "/products/\(id)", method: .get) }
    
    static let brands = APIEndpoint(path: "/brands", method: .get)
    static func brand(slug: String) -> APIEndpoint { APIEndpoint(path: "/brands/\(slug)", method: .get) }
    
    static let campaigns = APIEndpoint(path: "/campaigns", method: .get)
    
    static func login(data: Encodable) -> APIEndpoint { APIEndpoint(path: "/auth/login", method: .post, body: data) }
    static func register(data: Encodable) -> APIEndpoint { APIEndpoint(path: "/auth/register", method: .post, body: data) }
    static let logout = APIEndpoint(path: "/auth/logout", method: .post)
    static let user = APIEndpoint(path: "/auth/user", method: .get)
    
    static let cart = APIEndpoint(path: "/cart", method: .get)
    static func addToCart(data: Encodable) -> APIEndpoint { APIEndpoint(path: "/cart", method: .post, body: data) }
    static func updateCart(id: String, data: Encodable) -> APIEndpoint { APIEndpoint(path: "/cart/\(id)", method: .patch, body: data) }
    static func removeFromCart(id: String) -> APIEndpoint { APIEndpoint(path: "/cart/\(id)", method: .delete) }
    
    static func checkout(data: Encodable) -> APIEndpoint { APIEndpoint(path: "/checkout", method: .post, body: data) }
    
    static let addresses = APIEndpoint(path: "/addresses", method: .get)
    static func createAddress(data: Encodable) -> APIEndpoint { APIEndpoint(path: "/addresses", method: .post, body: data) }
    static func updateAddress(id: String, data: Encodable) -> APIEndpoint { APIEndpoint(path: "/addresses/\(id)", method: .patch, body: data) }
    static func deleteAddress(id: String) -> APIEndpoint { APIEndpoint(path: "/addresses/\(id)", method: .delete) }

    static let orders = APIEndpoint(path: "/orders", method: .get)
    static func order(id: String) -> APIEndpoint { APIEndpoint(path: "/orders/\(id)", method: .get) }
    static func startPayment(orderId: String) -> APIEndpoint { APIEndpoint(path: "/orders/\(orderId)/payment", method: .post) }

    static let notifications = APIEndpoint(path: "/notifications", method: .get)
    static let notificationUnreadCount = APIEndpoint(path: "/notifications/unread-count", method: .get)
    static func markNotificationRead(id: String) -> APIEndpoint { APIEndpoint(path: "/notifications/\(id)/read", method: .patch) }
    static let markAllNotificationsRead = APIEndpoint(path: "/notifications/read-all", method: .patch)
    static func registerPushDevice(data: Encodable) -> APIEndpoint { APIEndpoint(path: "/push-device-tokens", method: .post, body: data) }
}
