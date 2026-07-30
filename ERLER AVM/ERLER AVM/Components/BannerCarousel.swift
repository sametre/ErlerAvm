import SwiftUI

struct BannerCarousel: View {
    let banners: [Banner]
    @State private var currentIndex = 0
    
    var body: some View {
        VStack(spacing: 12) {
            TabView(selection: $currentIndex) {
                ForEach(0..<banners.count, id: \.self) { index in
                    let banner = banners[index]
                    
                    ZStack(alignment: .bottomLeading) {
                        Group {
                            if let url = APIEnvironment.assetURL(banner.imagePath) {
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
                                            .foregroundColor(.secondary)
                                    )
                            }
                        }
                        .frame(height: 176)
                        .clipped()
                        .cornerRadius(ErlerTheme.radius)
                        
                        LinearGradient(
                            gradient: Gradient(colors: [.black.opacity(0.62), .black.opacity(0.08)]),
                            startPoint: .bottom,
                            endPoint: .top
                        )
                        .cornerRadius(ErlerTheme.radius)
                        
                        VStack(alignment: .leading, spacing: 8) {
                            if let title = banner.title {
                                Text(title)
                                    .font(.title3.weight(.black))
                                    .foregroundColor(.white)
                                    .lineLimit(2)
                            }
                            if let subtitle = banner.subtitle {
                                Text(subtitle)
                                    .font(.footnote.weight(.medium))
                                    .foregroundColor(.white.opacity(0.9))
                                    .lineLimit(2)
                            }

                            if let label = banner.ctaLabel,
                               let url = resolvedURL(from: banner.ctaUrl) {
                                Link(destination: url) {
                                    HStack(spacing: 6) {
                                        Text(label)
                                        Image(systemName: "arrow.right")
                                    }
                                    .font(.caption.weight(.bold))
                                    .padding(.horizontal, 10)
                                    .padding(.vertical, 7)
                                    .background(Color.white)
                                    .foregroundColor(ErlerTheme.red)
                                    .cornerRadius(ErlerTheme.radius)
                                }
                            }
                        }
                        .padding()
                    }
                    .padding(.horizontal)
                    .tag(index)
                }
            }
            .frame(height: 176)
            .tabViewStyle(PageTabViewStyle(indexDisplayMode: .never))
            
            HStack(spacing: 6) {
                ForEach(0..<banners.count, id: \.self) { index in
                    Capsule()
                        .fill(currentIndex == index ? ErlerTheme.red : Color.gray.opacity(0.4))
                        .frame(width: currentIndex == index ? 18 : 6, height: 6)
                        .animation(.spring(), value: currentIndex)
                }
            }
        }
        .onAppear {
            setupAutoScroll()
        }
    }
    
    private func setupAutoScroll() {
        guard !banners.isEmpty else { return }

        Timer.scheduledTimer(withTimeInterval: 5.0, repeats: true) { _ in
            withAnimation {
                currentIndex = (currentIndex + 1) % banners.count
            }
        }
    }

    private func resolvedURL(from path: String?) -> URL? {
        guard let path, !path.isEmpty else { return nil }

        if path.hasPrefix("http://") || path.hasPrefix("https://") {
            return URL(string: path)
        }

        let normalizedPath = path.hasPrefix("/") ? path : "/\(path)"

        return URL(string: APIEnvironment.webBaseURL + normalizedPath)
    }
}
