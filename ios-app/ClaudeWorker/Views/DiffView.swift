import SwiftUI

struct DiffView: View {
    let title: String
    let content: String
    @Environment(\.dismiss) private var dismiss

    var body: some View {
        NavigationStack {
            ScrollView(.horizontal) {
                ScrollView(.vertical) {
                    VStack(alignment: .leading, spacing: 0) {
                        ForEach(Array(content.components(separatedBy: "\n").enumerated()), id: \.offset) { index, line in
                            HStack(spacing: 0) {
                                Text("\(index + 1)")
                                    .font(.system(.caption2, design: .monospaced))
                                    .foregroundStyle(.secondary)
                                    .frame(width: 40, alignment: .trailing)
                                    .padding(.trailing, 8)

                                Text(line)
                                    .font(.system(.caption, design: .monospaced))
                                    .foregroundStyle(lineColor(line))
                            }
                            .padding(.horizontal, 8)
                            .padding(.vertical, 1)
                            .frame(maxWidth: .infinity, alignment: .leading)
                            .background(lineBackground(line))
                        }
                    }
                    .padding(.vertical, 8)
                }
            }
            .navigationTitle(title)
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .topBarLeading) {
                    Button("Close") { dismiss() }
                }
                ToolbarItem(placement: .topBarTrailing) {
                    ShareLink(item: content) {
                        Image(systemName: "square.and.arrow.up")
                    }
                }
            }
        }
    }

    private func lineColor(_ line: String) -> Color {
        if line.hasPrefix("+++") || line.hasPrefix("---") {
            return .blue
        } else if line.hasPrefix("+") {
            return Color(red: 0.13, green: 0.77, blue: 0.37)
        } else if line.hasPrefix("-") {
            return Color(red: 0.94, green: 0.27, blue: 0.27)
        } else if line.hasPrefix("@@") {
            return .indigo
        } else if line.hasPrefix("diff ") {
            return .cyan
        }
        return .primary
    }

    private func lineBackground(_ line: String) -> Color {
        if line.hasPrefix("+") && !line.hasPrefix("+++") {
            return Color.green.opacity(0.08)
        } else if line.hasPrefix("-") && !line.hasPrefix("---") {
            return Color.red.opacity(0.08)
        } else if line.hasPrefix("@@") {
            return Color.indigo.opacity(0.06)
        }
        return .clear
    }
}
