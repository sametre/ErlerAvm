import Foundation
import Combine

@MainActor
class CartViewModel: ObservableObject {
    @Published var cart: CartResponse?
    @Published var isLoading = false
    @Published var errorMessage: String?
    
    func fetchCart() async {
        isLoading = true
        errorMessage = nil
        
        do {
            self.cart = try await APIClient.shared.request(.cart)
            self.isLoading = false
        } catch let error as APIError {
            self.errorMessage = error.errorDescription
            self.isLoading = false
        } catch {
            self.errorMessage = "Sepet bilgileri yüklenemedi."
            self.isLoading = false
        }
    }
    
    func updateQuantity(publicId: String, quantity: Int) async {
        do {
            struct UpdateCartRequest: Encodable { let quantity: Int }
            self.cart = try await APIClient.shared.request(.updateCart(id: publicId, data: UpdateCartRequest(quantity: quantity)))
        } catch {
            self.errorMessage = "Miktar güncellenemedi."
        }
    }
    
    func removeItem(publicId: String) async {
        do {
            self.cart = try await APIClient.shared.request(.removeFromCart(id: publicId))
        } catch {
            self.errorMessage = "Ürün silinemedi."
        }
    }
}
