import SwiftUI

struct ProductDetailView: View {
    @StateObject private var viewModel: ProductDetailViewModel
    @State private var selectedImageIndex = 0
    @State private var quantity = 1
    @State private var showingCartAlert = false
    
    init(productId: String) {
        _viewModel = StateObject(wrappedValue: ProductDetailViewModel(productId: productId))
    }
    
    var formattedPrice: String {
        guard let product = viewModel.product else { return "" }
        let formatter = NumberFormatter()
        formatter.numberStyle = .currency
        formatter.currencyCode = "TRY"
        let val = Double(product.price) ?? 0.0
        return formatter.string(from: NSNumber(value: val)) ?? "\(product.price) ₺"
    }
    
    var body: some View {
        ZStack {
            Color(.systemBackground).edgesIgnoringSafeArea(.all)
            
            if viewModel.isLoading && viewModel.product == nil {
                ProgressView()
            } else if let error = viewModel.errorMessage {
                VStack {
                    Text(error).foregroundColor(.red)
                    Button("Tekrar Dene") { Task { await viewModel.fetchProduct() } }
                }
            } else if let product = viewModel.product {
                VStack(spacing: 0) {
                    ScrollView {
                        VStack(alignment: .leading, spacing: 0) {
                            // Image Gallery
                            imageGallery(product: product)
                            
                            VStack(alignment: .leading, spacing: 16) {
                                // Title & Brand
                                VStack(alignment: .leading, spacing: 8) {
                                    if let brand = product.brand {
                                        Text(brand.name.uppercased())
                                            .font(.subheadline)
                                            .fontWeight(.bold)
                                            .foregroundColor(.gray)
                                    }
                                    
                                    Text(product.name)
                                        .font(.title2)
                                        .fontWeight(.semibold)
                                    
                                    HStack(alignment: .bottom, spacing: 8) {
                                        Text(formattedPrice)
                                            .font(.title)
                                            .fontWeight(.bold)
                                            .foregroundColor(ErlerTheme.red)
                                        
                                        if let oldPrice = product.compareAtPrice, let val = Double(oldPrice), val > 0 {
                                            Text("\(oldPrice) ₺")
                                                .font(.title3)
                                                .strikethrough()
                                                .foregroundColor(.gray)
                                        }
                                    }
                                }
                                .padding(.horizontal)
                                .padding(.top, 16)
                                
                                Divider().padding(.horizontal)
                                
                                // Description
                                if let desc = product.shortDescription ?? product.description {
                                    VStack(alignment: .leading, spacing: 8) {
                                        Text("Ürün Açıklaması")
                                            .font(.headline)
                                        Text(desc)
                                            .font(.body)
                                            .foregroundColor(.secondary)
                                            .lineSpacing(4)
                                    }
                                    .padding(.horizontal)
                                }
                                
                                // Reviews Placeholder
                                VStack(alignment: .leading, spacing: 8) {
                                    HStack {
                                        Text("Değerlendirmeler (\(product.reviewCount ?? 0))")
                                            .font(.headline)
                                        Spacer()
                                        HStack(spacing: 2) {
                                            Image(systemName: "star.fill").foregroundColor(.yellow)
                                            Text(String(format: "%.1f", product.averageRating ?? 0.0))
                                                .fontWeight(.bold)
                                        }
                                    }
                                }
                                .padding()
                                .background(Color(.systemGray6))
                                .cornerRadius(12)
                                .padding(.horizontal)
                                
                                Spacer().frame(height: 100) // Padding for bottom bar
                            }
                        }
                    }
                    .edgesIgnoringSafeArea(.top)
                    
                    // Bottom Action Bar
                    bottomBar(product: product)
                }
            }
        }
        .task {
            await viewModel.fetchProduct()
        }
        .alert(isPresented: $showingCartAlert) {
            Alert(title: Text("Sepet"), message: Text(viewModel.cartMessage ?? ""), dismissButton: .default(Text("Tamam")))
        }
        .onChange(of: viewModel.cartMessage) { message in
            if message != nil {
                showingCartAlert = true
            }
        }
    }
    
    @ViewBuilder
    private func imageGallery(product: Product) -> some View {
        let images = product.images
        
        ZStack(alignment: .bottom) {
            if images.isEmpty {
                Rectangle()
                    .fill(Color(.systemGray5))
                    .overlay(
                        Image(systemName: "photo")
                            .font(.system(size: 60))
                            .foregroundColor(.gray)
                    )
                    .frame(height: 350)
            } else {
                TabView(selection: $selectedImageIndex) {
                    ForEach(0..<images.count, id: \.self) { index in
                        Group {
                            if let url = APIEnvironment.assetURL(images[index]) {
                                AsyncImage(url: url) { image in
                                    image
                                        .resizable()
                                        .aspectRatio(contentMode: .fill)
                                } placeholder: {
                                    Color(.systemGray5)
                                }
                            } else {
                                Color(.systemGray5)
                                    .overlay(
                                        Image(systemName: "photo")
                                            .font(.system(size: 60))
                                            .foregroundColor(.gray)
                                    )
                            }
                        }
                        .tag(index)
                    }
                }
                .frame(height: 350)
                .tabViewStyle(PageTabViewStyle(indexDisplayMode: .always))
            }
        }
    }
    
    @ViewBuilder
    private func bottomBar(product: Product) -> some View {
        VStack(spacing: 0) {
            Divider()
            HStack(spacing: 16) {
                // Quantity Selector
                HStack {
                    Button(action: { if quantity > 1 { quantity -= 1 } }) {
                        Image(systemName: "minus")
                            .frame(width: 30, height: 30)
                            .foregroundColor(.primary)
                    }
                    Text("\(quantity)")
                        .fontWeight(.semibold)
                        .frame(minWidth: 20)
                    Button(action: { if quantity < product.stockQuantity { quantity += 1 } }) {
                        Image(systemName: "plus")
                            .frame(width: 30, height: 30)
                            .foregroundColor(.primary)
                    }
                }
                .padding(.vertical, 8)
                .padding(.horizontal, 12)
                .background(Color(.systemGray6))
                .cornerRadius(8)
                
                // Add to Cart Button
                Button(action: {
                    Task { await viewModel.addToCart(quantity: quantity) }
                }) {
                    HStack {
                        if viewModel.isAddingToCart {
                            ProgressView().progressViewStyle(CircularProgressViewStyle(tint: .white))
                        } else {
                            Text("Sepete Ekle")
                                .fontWeight(.bold)
                        }
                    }
                    .frame(maxWidth: .infinity)
                    .padding(.vertical, 14)
                    .background(product.stockQuantity > 0 ? ErlerTheme.red : Color.gray)
                    .foregroundColor(.white)
                    .cornerRadius(12)
                }
                .disabled(product.stockQuantity <= 0 || viewModel.isAddingToCart)
            }
            .padding(.horizontal)
            .padding(.vertical, 12)
            .background(Color(.systemBackground))
        }
    }
}
