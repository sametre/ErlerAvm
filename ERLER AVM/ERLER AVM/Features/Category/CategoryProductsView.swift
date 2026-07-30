import SwiftUI
import Combine

struct CategoryProductsView: View {
    @StateObject private var viewModel: CategoryProductsViewModel
    private let categoryName: String

    init(categorySlug: String, categoryName: String) {
        self.categoryName = categoryName
        _viewModel = StateObject(wrappedValue: CategoryProductsViewModel(categorySlug: categorySlug))
    }

    var body: some View {
        ZStack {
            ErlerTheme.softBackground.edgesIgnoringSafeArea(.all)

            if viewModel.isLoading && viewModel.products.isEmpty {
                ProgressView()
            } else if let error = viewModel.errorMessage, viewModel.products.isEmpty {
                VStack(spacing: 12) {
                    Text(error)
                        .foregroundColor(.red)
                    Button("Tekrar Dene") {
                        Task { await viewModel.fetchProducts() }
                    }
                }
            } else if viewModel.products.isEmpty {
                VStack(spacing: 12) {
                    Image(systemName: "shippingbox")
                        .font(.system(size: 54))
                        .foregroundColor(.secondary)
                    Text("Bu kategoride yayında ürün yok.")
                        .foregroundColor(.secondary)
                }
            } else {
                ScrollView {
                    LazyVGrid(columns: [GridItem(.flexible(), spacing: 14), GridItem(.flexible(), spacing: 14)], spacing: 14) {
                        ForEach(viewModel.products) { product in
                            NavigationLink(destination: ProductDetailView(productId: product.id)) {
                                ProductCardView(product: product)
                                    .frame(maxWidth: .infinity)
                            }
                            .buttonStyle(.plain)
                        }
                    }
                    .padding()
                }
                .refreshable {
                    await viewModel.fetchProducts()
                }
            }
        }
        .navigationTitle(viewModel.category?.name ?? categoryName)
        .navigationBarTitleDisplayMode(.inline)
        .task {
            await viewModel.fetchProducts()
        }
    }
}

private struct CategoryProductsResponse: Codable {
    let category: Category
    let products: ProductPage
}

private struct ProductPage: Codable {
    let data: [Product]
}

@MainActor
final class CategoryProductsViewModel: ObservableObject {
    @Published var category: Category?
    @Published var products: [Product] = []
    @Published var isLoading = false
    @Published var errorMessage: String?

    private let categorySlug: String

    init(categorySlug: String) {
        self.categorySlug = categorySlug
    }

    func fetchProducts() async {
        guard !isLoading else { return }

        isLoading = true
        errorMessage = nil

        do {
            let response: CategoryProductsResponse = try await APIClient.shared.request(.category(slug: categorySlug))
            category = response.category
            products = response.products.data
            isLoading = false
        } catch let error as APIError {
            errorMessage = error.errorDescription
            isLoading = false
        } catch {
            errorMessage = "Kategori ürünleri yüklenemedi."
            isLoading = false
        }
    }
}
