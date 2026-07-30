import SwiftUI

struct HomeView: View {
    @StateObject private var viewModel = HomeViewModel()
    @State private var searchText = ""

    private var visibleProducts: [Product] {
        let query = searchText.trimmingCharacters(in: .whitespacesAndNewlines)
        guard !query.isEmpty else { return viewModel.featuredProducts }

        return viewModel.featuredProducts.filter { product in
            product.name.localizedCaseInsensitiveContains(query)
                || (product.brand?.name.localizedCaseInsensitiveContains(query) ?? false)
                || (product.category?.name.localizedCaseInsensitiveContains(query) ?? false)
        }
    }
    
    var body: some View {
        NavigationStack {
            ZStack(alignment: .top) {
                ErlerTheme.softBackground.ignoresSafeArea()
                
                if viewModel.isLoading && viewModel.featuredProducts.isEmpty {
                    ProgressView()
                } else if let error = viewModel.errorMessage, viewModel.featuredProducts.isEmpty {
                    VStack {
                        Text(error)
                            .foregroundColor(.red)
                        Button("Tekrar Dene") {
                            Task { await viewModel.fetchHomeData() }
                        }
                        .padding()
                    }
                } else {
                    ScrollView(showsIndicators: false) {
                        VStack(spacing: 22) {
                            header
                            
                            if !viewModel.banners.isEmpty {
                                BannerCarousel(banners: viewModel.banners)
                            }
                            
                            if !viewModel.categories.isEmpty {
                                categoriesSection
                            }
                            
                            if !visibleProducts.isEmpty {
                                featuredProductsSection
                            } else if !searchText.isEmpty {
                                VStack(spacing: 8) {
                                    Image(systemName: "magnifyingglass")
                                        .font(.title)
                                        .foregroundColor(.secondary)
                                    Text("Aramanıza uygun ürün bulunamadı.")
                                        .font(.subheadline)
                                        .foregroundColor(.secondary)
                                }
                                .frame(maxWidth: .infinity)
                                .padding(.vertical, 24)
                            }
                            
                            if !viewModel.brands.isEmpty {
                                brandsSection
                            }
                            
                            Spacer().frame(height: 40)
                        }
                    }
                    .refreshable {
                        await viewModel.fetchHomeData()
                    }
                }
            }
            .toolbar(.hidden, for: .navigationBar)
        }
        .task {
            await viewModel.fetchHomeData()
        }
    }
    
    private var header: some View {
        VStack(spacing: 18) {
            HStack(spacing: 12) {
                Image("Logo")
                    .resizable()
                    .scaledToFit()
                    .frame(width: 42, height: 42)
                    .clipShape(RoundedRectangle(cornerRadius: ErlerTheme.radius, style: .continuous))
                    .overlay(
                        RoundedRectangle(cornerRadius: ErlerTheme.radius, style: .continuous)
                            .stroke(Color.white.opacity(0.9), lineWidth: 2)
                    )

                VStack(alignment: .leading, spacing: 2) {
                    Text("ERLER AVM")
                        .font(.title3.weight(.black))
                        .foregroundColor(.white)

                    Text("Karacabey")
                        .font(.caption.weight(.semibold))
                        .foregroundColor(.white.opacity(0.76))
                }

                Spacer()

                NavigationLink(destination: NotificationsView()) {
                    Image(systemName: "bell.fill")
                        .erlerToolbarIcon()
                }
            }

            searchBar
        }
        .padding(.horizontal, 18)
        .padding(.top, 18)
        .padding(.bottom, 22)
        .background(ErlerTheme.red)
    }

    private var searchBar: some View {
        HStack(spacing: 10) {
            Image(systemName: "magnifyingglass")
                .font(.system(size: 15, weight: .semibold))
                .foregroundColor(ErlerTheme.graphite)

            TextField("Ürün, kategori veya marka ara", text: $searchText)
                .font(.subheadline)

            if !searchText.isEmpty {
                Button {
                    searchText = ""
                } label: {
                    Image(systemName: "xmark.circle.fill")
                        .foregroundColor(.secondary)
                }
            }
        }
        .padding(.horizontal, 14)
        .frame(height: 46)
        .background(Color.white)
        .cornerRadius(ErlerTheme.radius)
        .shadow(color: Color.black.opacity(0.12), radius: 10, x: 0, y: 5)
    }
    
    private var categoriesSection: some View {
        VStack(alignment: .leading, spacing: 14) {
            sectionHeader(title: "Kategoriler", destination: AnyView(CategoryListView()))
            
            ScrollView(.horizontal, showsIndicators: false) {
                HStack(spacing: 14) {
                    ForEach(viewModel.categories) { category in
                        CategoryCircleView(category: category)
                    }
                }
                .padding(.horizontal)
            }
        }
    }
    
    private var featuredProductsSection: some View {
        VStack(alignment: .leading, spacing: 14) {
            sectionHeader(title: "Öne Çıkan Ürünler")
            
            ScrollView(.horizontal, showsIndicators: false) {
                HStack(spacing: 14) {
                    ForEach(visibleProducts) { product in
                        NavigationLink(destination: ProductDetailView(productId: product.id)) {
                            ProductCardView(product: product)
                        }
                        .buttonStyle(PlainButtonStyle())
                    }
                }
                .padding(.horizontal)
            }
        }
    }
    
    private var brandsSection: some View {
        VStack(alignment: .leading, spacing: 14) {
            sectionHeader(title: "Popüler Markalar")
            
            ScrollView(.horizontal, showsIndicators: false) {
                HStack(spacing: 12) {
                    ForEach(viewModel.brands) { brand in
                        VStack(spacing: 8) {
                            Text(String(brand.name.prefix(1)))
                                .font(.title3.weight(.black))
                                .foregroundColor(ErlerTheme.red)
                                .frame(width: 58, height: 58)
                                .background(Color.white)
                                .clipShape(Circle())
                                .overlay(Circle().stroke(ErlerTheme.line, lineWidth: 1))

                            Text(brand.name)
                                .font(.caption)
                                .fontWeight(.semibold)
                                .foregroundColor(ErlerTheme.ink)
                                .lineLimit(1)
                                .frame(width: 76)
                        }
                    }
                }
                .padding(.horizontal)
            }
        }
    }

    @ViewBuilder
    private func sectionHeader(title: String, destination: AnyView? = nil) -> some View {
        HStack {
            Text(title)
                .font(.title3.weight(.bold))
                .foregroundColor(ErlerTheme.ink)

            Spacer()

            if let destination {
                NavigationLink(destination: destination) {
                    HStack(spacing: 4) {
                        Text("Tümü")
                        Image(systemName: "chevron.right")
                            .font(.caption2.weight(.bold))
                    }
                    .font(.footnote.weight(.bold))
                    .foregroundColor(ErlerTheme.red)
                }
            }
        }
        .padding(.horizontal)
    }
}
