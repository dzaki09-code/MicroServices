// src/pages/ProductDetailPage.jsx
import { useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { getProductDetail } from '../api/aggregate'
import { addToCart } from '../api/cartApi'
import { deleteReview } from '../api/reviewApi'
import AsyncView from '../components/AsyncView'
import ReviewForm from '../components/ReviewForm'
import StarRating from '../components/StarRating'
import { useAsync } from '../hooks/useAsync'
import { describeError, formatRupiah } from '../utils/format'
import { getProductImageSrc } from '../utils/productImage'

export default function ProductDetailPage({ userId, onCartChange }) {
  const { id } = useParams()

  const { loading, data, error, reload } = useAsync(
    () => getProductDetail(id),
    [id]
  )

  const [quantity, setQuantity] = useState('1')
  const [cartMessage, setCartMessage] = useState(null)
  const [reviewActionError, setReviewActionError] = useState(null)
  const [deletingReviewId, setDeletingReviewId] = useState(null)

  async function handleAddToCart() {
    const amount = Math.max(1, Number(quantity) || 1)
    try {
      await addToCart(userId, Number(id), amount)
      setCartMessage({
        type: 'success',
        text: '✅ Produk berhasil ditambahkan ke keranjang!',
      })
      if (onCartChange) await onCartChange()
    } catch (err) {
      setCartMessage({
        type: 'error',
        text: describeError(err),
      })
    }
  }

  async function handleDeleteReview(review) {
    if (!window.confirm('Yakin ingin menghapus ulasan ini?')) return

    setReviewActionError(null)
    setDeletingReviewId(review.id)

    try {
      await deleteReview(review.id)
      reload()
    } catch (err) {
      setReviewActionError(err)
    } finally {
      setDeletingReviewId(null)
    }
  }

  return (
    <section>
      {/* Breadcrumb */}
      <div className="breadcrumb">
        <Link to="/">Beranda</Link>
        <span className="breadcrumb-sep">/</span>
        <Link to="/">Produk</Link>
        <span className="breadcrumb-sep">/</span>
        <span className="breadcrumb-current">
          {data ? data.product.name : '...'}
        </span>
      </div>

      <AsyncView loading={loading} error={error} onRetry={reload}>
        {data && (
          <>
            <div className="detail-layout">
              {/* Left: product image */}
              <div className="product-img-placeholder">
                {data.product.image ? (
                  <img
                    src={getProductImageSrc(data.product.image)}
                    alt={data.product.name}
                  />
                ) : (
                  <span className="image-missing">Gambar tidak tersedia</span>
                )}
              </div>

              {/* Right: product info */}
              <div>
                <h1>{data.product.name}</h1>

                <p className="price">{formatRupiah(data.product.price)}</p>

                {data.product.stock > 0 ? (
                  <span className="badge-instock">
                    ✓ Stok tersedia: {data.product.stock} unit
                  </span>
                ) : (
                  <span className="badge-outofstock">✗ Stok Habis</span>
                )}

                {data.reviews && (
                  <div style={{ marginTop: '0.75rem' }}>
                    <StarRating
                      average={data.reviews.summary.average_rating}
                      total={data.reviews.summary.total_reviews}
                    />
                  </div>
                )}

                <p style={{ marginTop: '1rem', color: 'var(--muted)', lineHeight: 1.7 }}>
                  {data.product.description}
                </p>

                <div className="actions">
                  <input
                    type="number"
                    min="1"
                    max={data.product.stock}
                    value={quantity}
                    aria-label="Jumlah"
                    onChange={(e) => setQuantity(e.target.value)}
                  />

                  <button
                    type="button"
                    onClick={handleAddToCart}
                    disabled={data.product.stock === 0}
                  >
                    🛒 Masukkan ke Keranjang
                  </button>
                </div>

                {cartMessage && (
                  <p className={`alert alert-${cartMessage.type}`}>
                    {cartMessage.text}
                  </p>
                )}
              </div>
            </div>

            {/* Reviews */}
            <h2>Ulasan Pembeli</h2>

            {data.reviews ? (
              <>
                {reviewActionError && (
                  <p className="alert alert-error">
                    {describeError(reviewActionError)}
                  </p>
                )}

                <ul className="reviews">
                  {data.reviews.reviews.map((review) => (
                    <li key={review.id}>
                      <StarRating
                        average={review.rating}
                        total={1}
                        compact
                      />
                      <p>
                        {review.comment || (
                          <em className="muted">Tanpa komentar</em>
                        )}
                      </p>
                      <small className="muted">
                        User {review.user_id},{' '}
                        {new Date(review.created_at).toLocaleDateString('id-ID')}
                      </small>
                      {review.user_id === userId && (
                        <div>
                          <button
                            type="button"
                            className="btn-danger review-delete-button"
                            disabled={deletingReviewId === review.id}
                            onClick={() => handleDeleteReview(review)}
                          >
                            {deletingReviewId === review.id
                              ? 'Menghapus...'
                              : 'Hapus Ulasan'}
                          </button>
                        </div>
                      )}
                    </li>
                  ))}

                  {data.reviews.reviews.length === 0 && (
                    <li className="muted">
                      Belum ada ulasan untuk produk ini.
                    </li>
                  )}
                </ul>
              </>
            ) : (
              <p className="alert alert-error">
                Ulasan tidak dapat dimuat saat ini.
              </p>
            )}

            <ReviewForm
              productId={Number(id)}
              userId={userId}
              onCreated={reload}
            />
          </>
        )}
      </AsyncView>
    </section>
  )
}
