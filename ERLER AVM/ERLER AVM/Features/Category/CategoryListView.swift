import SwiftUI

struct CategoryListView: View {
    @StateObject private var viewModel = CategoryViewModel()
    
    var body: some View {
        NavigationView {
            ZStack {
                Color(.systemGroupedBackground).edgesIgnoringSafeArea(.all)
                
                if viewModel.isLoading && viewModel.categories.isEmpty {
                    ProgressView()
                } else if let error = viewModel.errorMessage, viewModel.categories.isEmpty {
                    VStack {
                        Text(error).foregroundColor(.red)
                        Button("Tekrar Dene") { Task { await viewModel.fetchCategories() } }
                    }
                } else {
                    ScrollView {
                        LazyVGrid(columns: [GridItem(.flexible(), spacing: 16), GridItem(.flexible(), spacing: 16)], spacing: 16) {
                            ForEach(viewModel.categories) { category in
                                NavigationLink(destination: CategoryProductsView(categorySlug: category.slug, categoryName: category.name)) {
                                    CategoryCardView(category: category)
                                }
                                .buttonStyle(.plain)
                            }
                        }
                        .padding()
                    }
                }
            }
            .navigationTitle("Kategoriler")
        }
        .task {
            await viewModel.fetchCategories()
        }
    }
}

struct CategoryCardView: View {
    let category: Category
    
    var iconName: String {
        let name = category.name.lowercased()
        if name.contains("telefon") || name.contains("teknoloji") { return "iphone" }
        if name.contains("bilgisayar") || name.contains("laptop") { return "laptopcomputer" }
        if name.contains("ev") || name.contains("yaşam") { return "house.fill" }
        if name.contains("beyaz") { return "refrigerator" }
        return "bag"
    }
    
    var body: some View {
        VStack(spacing: 12) {
            Image(systemName: iconName)
                .font(.system(size: 40))
                .foregroundColor(ErlerTheme.red)
            
            Text(category.name)
                .font(.headline)
                .foregroundColor(.primary)
                .multilineTextAlignment(.center)
                .lineLimit(2)
        }
        .frame(maxWidth: .infinity)
        .frame(height: 120)
        .background(Color(.systemBackground))
        .cornerRadius(16)
        .shadow(color: Color.black.opacity(0.05), radius: 5, x: 0, y: 2)
    }
}
