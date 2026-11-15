// src/components/ProductCard.jsx
import { Link } from 'react-router-dom'
import { formatRupiah } from '../utils/format'
import { getProductImageSrc } from '../utils/productImage'
import StarRating from './StarRating'

export default function ProductCard({ product }) {
  const { id, name, price, stock, rating, image } = product

  return (
    <article className="card">
      <div className="card-img">
        {image ? (
          <img
            src={getProductImageSrc(image)}
            alt={name}
            loading="lazy"
          />
        ) : (
          <span className="image-missing">Gambar tidak tersedia</span>
        )}
      </div>

      <div className="card-body">
        <h3>
          <Link to={`/products/${id}`}>{name}</Link>
        </h3>

        <p className="price">{formatRupiah(price)}</p>

        {stock > 0 ? (
          <span className="badge-instock">✓ Stok: {stock} unit</span>
        ) : (
          <span className="badge-outofstock">✗ Stok Habis</span>
        )}

        {rating ? (
          <StarRating
            average={rating.average_rating}
            total={rating.total_reviews}
          />
        ) : (
          <span className="muted">Belum ada ulasan</span>
        )}
      </div>
    </article>
  )
}
