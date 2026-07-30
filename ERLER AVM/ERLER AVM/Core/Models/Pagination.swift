import Foundation

struct PaginatedList<T: Codable>: Codable {
    let data: [T]
    let meta: APIMeta?
}
