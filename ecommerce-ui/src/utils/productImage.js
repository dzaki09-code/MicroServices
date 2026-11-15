export function getProductImageSrc(image) {
  if (image.startsWith('data:image/')) {
    return image
  }

  const mimeType = image.startsWith('PHN2Zy') || image.startsWith('PD94bWwg')
    ? 'image/svg+xml'
    : image.startsWith('/9j/')
      ? 'image/jpeg'
      : image.startsWith('R0lGOD')
        ? 'image/gif'
        : image.startsWith('UklGR')
          ? 'image/webp'
          : 'image/png'

  return `data:${mimeType};base64,${image}`
}
