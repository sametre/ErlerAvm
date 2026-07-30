import Foundation

struct NotificationsResponse: Codable {
    let notifications: [AppNotification]
    let unreadCount: Int

    enum CodingKeys: String, CodingKey {
        case notifications
        case unreadCount = "unread_count"
    }
}

struct AppNotification: Codable, Identifiable {
    var id: String { publicId }

    let publicId: String
    let type: String
    let title: String
    let body: String?
    let actionUrl: String?
    let readAt: String?
    let createdAt: String?

    var isUnread: Bool { readAt == nil }

    enum CodingKeys: String, CodingKey {
        case publicId = "public_id"
        case type, title, body
        case actionUrl = "action_url"
        case readAt = "read_at"
        case createdAt = "created_at"
    }
}

struct NotificationUnreadCountResponse: Codable {
    let unreadCount: Int

    enum CodingKeys: String, CodingKey {
        case unreadCount = "unread_count"
    }
}

struct PushDeviceTokenRequest: Encodable {
    let platform: String
    let token: String
    let deviceId: String?
    let appVersion: String?
    let locale: String?
    let timezone: String?

    enum CodingKeys: String, CodingKey {
        case platform, token, locale, timezone
        case deviceId = "device_id"
        case appVersion = "app_version"
    }
}

struct PushDeviceRegistrationResponse: Codable {
    let registered: Bool
    let platform: String
    let lastSeenAt: String?

    enum CodingKeys: String, CodingKey {
        case registered, platform
        case lastSeenAt = "last_seen_at"
    }
}
