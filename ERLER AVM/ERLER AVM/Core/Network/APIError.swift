import Foundation

enum APIError: Error, LocalizedError {
    case invalidURL
    case invalidResponse
    case unauthenticated
    case serverError(message: String)
    case decodingError
    case networkError(Error)
    
    var errorDescription: String? {
        switch self {
        case .invalidURL: return "Geçersiz URL."
        case .invalidResponse: return "Sunucudan geçersiz bir yanıt alındı."
        case .unauthenticated: return "Oturum süreniz doldu, lütfen tekrar giriş yapın."
        case .serverError(let message): return message
        case .decodingError: return "Veri işlenirken bir hata oluştu."
        case .networkError(let error):
            if let urlError = error as? URLError {
                switch urlError.code {
                case .cannotFindHost, .cannotConnectToHost, .dnsLookupFailed:
                    return "Sunucuya ulaşılamıyor. Lütfen internet bağlantınızı veya API adresini kontrol edin."
                case .notConnectedToInternet, .networkConnectionLost:
                    return "İnternet bağlantısı yok veya bağlantı kesildi."
                case .timedOut:
                    return "Bağlantı zaman aşımına uğradı. Lütfen tekrar deneyin."
                case .secureConnectionFailed,
                    .serverCertificateHasBadDate,
                    .serverCertificateHasUnknownRoot,
                    .serverCertificateNotYetValid,
                    .serverCertificateUntrusted,
                    .clientCertificateRejected,
                    .clientCertificateRequired:
                    return "Güvenli bağlantı kurulamadı. Lokal geliştirme sertifikasını veya API adresini kontrol edin."
                default:
                    break
                }
            }

            return "Bağlantı hatası: \(error.localizedDescription)"
        }
    }
}
