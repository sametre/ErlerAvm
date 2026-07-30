import Foundation
import Combine

@MainActor
class HomeViewModel: ObservableObject {
    @Published var banners: [Banner] = []
    @Published var categories: [Category] = []
    @Published var brands: [Brand] = []
    @Published var featuredProducts: [Product] = []
    
    @Published var isLoading = false
    @Published var errorMessage: String?
    
    func fetchHomeData() async {
        guard !isLoading else { return }
        isLoading = true
        errorMessage = nil
        
        do {
            let response: HomeResponse = try await APIClient.shared.request(.home)
            self.banners = response.banners["home_hero"] ?? response.banners["hero"] ?? response.banners.values.first ?? []
            self.categories = response.categories
            self.brands = response.brands
            self.featuredProducts = response.featuredProducts
            self.isLoading = false
        } catch let error as APIError {
            self.errorMessage = error.errorDescription
            self.isLoading = false
        } catch {
            self.errorMessage = "Beklenmeyen bir hata oluştu."
            self.isLoading = false
        }
    }
}
