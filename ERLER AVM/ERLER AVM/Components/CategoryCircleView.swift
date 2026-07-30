import SwiftUI

struct CategoryCircleView: View {
    let category: Category
    
    var iconName: String {
        let name = category.name.lowercased()
        if name.contains("telefon") || name.contains("teknoloji") { return "iphone" }
        if name.contains("bilgisayar") || name.contains("laptop") { return "laptopcomputer" }
        if name.contains("ev") || name.contains("yaşam") { return "house.fill" }
        if name.contains("beyaz") { return "refrigerator" }
        if name.contains("moda") || name.contains("giyim") { return "tshirt" }
        return "square.grid.2x2"
    }
    
    var body: some View {
        NavigationLink(destination: CategoryProductsView(categorySlug: category.slug, categoryName: category.name)) {
            VStack(spacing: 9) {
                ZStack {
                    Circle()
                        .fill(Color.white)
                        .frame(width: 62, height: 62)
                        .overlay(Circle().stroke(ErlerTheme.line, lineWidth: 1))
                    
                    Image(systemName: iconName)
                        .font(.system(size: 23, weight: .semibold))
                        .foregroundColor(ErlerTheme.red)
                }
                
                Text(category.name)
                    .font(.caption)
                    .fontWeight(.semibold)
                    .foregroundColor(ErlerTheme.ink)
                    .lineLimit(2)
                    .multilineTextAlignment(.center)
                    .frame(width: 78, height: 32, alignment: .top)
            }
        }
        .buttonStyle(.plain)
    }
}
