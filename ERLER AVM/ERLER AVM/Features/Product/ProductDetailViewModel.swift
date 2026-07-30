import Foundation
import Combine

@MainActor
class ProductDetailViewModel: ObservableObject {
    @Published var product: Product?
    @Published var isLoading = false
    @Published var errorMessage: String?
    @Published var isAddingToCart = false
    @Published var cartMessage: String?
    
    let productId: String
    
    init(productId: String) {
        self.productId = productId
    }
    
    func fetchProduct() async {
        guard !isLoading else { return }
        isLoading = true
        errorMessage = nil
        
        do {
            self.product = try await APIClient.shared.request(.product(id: productId))
            self.isLoading = false
        } catch let error as APIError {
            self.errorMessage = error.errorDescription
            self.isLoading = false
        } catch {
            self.errorMessage = "Ürün bilgileri yüklenemedi."
            self.isLoading = false
        }
    }
    
    func addToCart(quantity: Int = 1) async {
        guard let product = product else { return }
        isAddingToCart = true
        cartMessage = nil
        
        do {
            struct AddCartRequest: Encodable {
                let public_id: String
                let quantity: Int
            }
            let reqData = AddCartRequest(public_id: product.id, quantity: quantity)

            let _: CartResponse = try await APIClient.shared.request(.addToCart(data: reqData), useCache: false)
            self.cartMessage = "Ürün sepete eklendi."
            self.isAddingToCart = false
        } catch let error as APIError {
            self.cartMessage = error.errorDescription
            self.isAddingToCart = false
        } catch {
            self.cartMessage = "Sepete eklenirken bir hata oluştu."
            self.isAddingToCart = false
        }
    }
}
