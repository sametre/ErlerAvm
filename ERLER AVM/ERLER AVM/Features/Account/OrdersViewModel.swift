import Foundation
import Combine

struct PaginatedOrdersResponse: Codable {
    let data: [OrderShort]
    let meta: APIMeta
}

@MainActor
class OrdersViewModel: ObservableObject {
    @Published var orders: [OrderShort] = []
    @Published var isLoading = false
    @Published var errorMessage: String?
    
    func fetchOrders() async {
        guard !isLoading else { return }
        isLoading = true
        errorMessage = nil
        
        do {
            let response: PaginatedList<OrderShort> = try await APIClient.shared.requestPage(.orders)
            self.orders = response.data
            self.isLoading = false
        } catch let error as APIError {
            self.errorMessage = error.errorDescription
            self.isLoading = false
        } catch {
            self.errorMessage = "Siparişler yüklenemedi."
            self.isLoading = false
        }
    }
}
