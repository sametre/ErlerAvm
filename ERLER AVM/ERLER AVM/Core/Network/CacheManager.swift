import Foundation

final class CacheManager {
    static let shared = CacheManager()
    private let userDefaults = UserDefaults.standard
    
    private init() {}
    
    func save<T: Encodable>(data: T, for key: String) {
        if let encoded = try? JSONEncoder().encode(data) {
            userDefaults.set(encoded, forKey: "cache_\(key)")
        }
    }
    
    func load<T: Decodable>(for key: String, as type: T.Type) -> T? {
        if let data = userDefaults.data(forKey: "cache_\(key)") {
            return try? JSONDecoder().decode(type, from: data)
        }
        return nil
    }
    
    func clearCache(for key: String) {
        userDefaults.removeObject(forKey: "cache_\(key)")
    }

    func clearAll() {
        for key in userDefaults.dictionaryRepresentation().keys where key.hasPrefix("cache_") {
            userDefaults.removeObject(forKey: key)
        }
    }
}
