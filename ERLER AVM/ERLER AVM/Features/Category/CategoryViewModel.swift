import Foundation
import Combine

@MainActor
class CategoryViewModel: ObservableObject {
    @Published var categories: [Category] = []
    @Published var isLoading = false
    @Published var errorMessage: String?
    
    func fetchCategories() async {
        guard categories.isEmpty, !isLoading else { return }
        isLoading = true
        errorMessage = nil
        
        do {
            self.categories = try await APIClient.shared.request(.categories)
            self.isLoading = false
        } catch let error as APIError {
            self.errorMessage = error.errorDescription
            self.isLoading = false
        } catch {
            self.errorMessage = "Kategoriler yüklenemedi."
            self.isLoading = false
        }
    }
}
