import SwiftUI

struct ProductCardView: View {
    let product: Product
    
    var formattedPrice: String {
        let formatter = NumberFormatter()
        formatter.numberStyle = .currency
        formatter.currencyCode = "TRY"
        formatter.currencySymbol = "₺"
        
        let priceValue = Double(product.price) ?? 0.0
        return formatter.string(from: NSNumber(value: priceValue)) ?? "\(product.price) ₺"
    }
    
    var formattedComparePrice: String? {
        guard let comparePrice = product.compareAtPrice, let val = Double(comparePrice), val > 0 else { return nil }
        let formatter = NumberFormatter()
        formatter.numberStyle = .currency
        formatter.currencyCode = "TRY"
        formatter.currencySymbol = "₺"
        return formatter.string(from: NSNumber(value: val))
    }

    var discountText: String? {
        guard let comparePrice = product.compareAtPrice,
              let old = Double(comparePrice),
              let current = Double(product.price),
              old > current,
              old > 0 else {
            return nil
        }

        let percent = Int(((old - current) / old * 100).rounded())

        return "%\(percent)"
    }
    
    var body: some View {
        VStack(alignment: .leading, spacing: 10) {
            ZStack(alignment: .topLeading) {
                if let url = APIEnvironment.assetURL(product.images.first) {
                    AsyncImage(url: url) { image in
                        image
                            .resizable()
                            .aspectRatio(contentMode: .fill)
                    } placeholder: {
                        ProgressView()
                    }
                } else {
                    Image(systemName: "photo")
                        .font(.title2)
                        .foregroundColor(.gray)
                }

                if let discountText {
                    Text(discountText)
                        .font(.caption2.weight(.black))
                        .padding(.horizontal, 7)
                        .padding(.vertical, 5)
                        .background(ErlerTheme.red)
                        .foregroundColor(.white)
                        .cornerRadius(ErlerTheme.radius)
                        .padding(8)
                }
            }
            .frame(height: 136)
            .frame(maxWidth: .infinity)
            .background(Color(.systemGray6))
            .clipped()
            .cornerRadius(ErlerTheme.radius)
            
            VStack(alignment: .leading, spacing: 6) {
                if let brand = product.brand {
                    Text(brand.name.uppercased())
                        .font(.system(size: 10, weight: .bold))
                        .foregroundColor(ErlerTheme.graphite)
                        .lineLimit(1)
                }
                
                Text(product.name)
                    .font(.subheadline)
                    .fontWeight(.semibold)
                    .foregroundColor(ErlerTheme.ink)
                    .lineLimit(2)
                    .frame(height: 40, alignment: .topLeading)
                
                HStack(alignment: .lastTextBaseline, spacing: 5) {
                    Text(formattedPrice)
                        .font(.headline)
                        .fontWeight(.black)
                        .foregroundColor(ErlerTheme.red)
                    
                    if let oldPrice = formattedComparePrice {
                        Text(oldPrice)
                            .font(.caption)
                            .strikethrough()
                            .foregroundColor(.gray)
                    }
                }

                HStack(spacing: 5) {
                    Circle()
                        .fill(product.stockQuantity > 0 ? Color.green : Color.gray)
                        .frame(width: 6, height: 6)

                    Text(product.stockQuantity > 0 ? "Stokta" : "Tükendi")
                        .font(.caption2.weight(.semibold))
                        .foregroundColor(.secondary)
                }
            }
            .padding(.horizontal, 2)
            .padding(.bottom, 8)
        }
        .padding(10)
        .frame(width: 166)
        .background(Color(.systemBackground))
        .cornerRadius(ErlerTheme.radius)
        .shadow(color: ErlerTheme.softShadow, radius: 12, x: 0, y: 8)
    }
}
