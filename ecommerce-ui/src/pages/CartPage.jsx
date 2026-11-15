// src/pages/CartPage.jsx
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { getCartDetail } from '../api/aggregate'
import {
  clearCart,
  removeCartItem,
  updateCartItem,
} from '../api/cartApi'
import AsyncView from '../components/AsyncView'
import { useAsync } from '../hooks/useAsync'
import { describeError, formatRupiah } from '../utils/format'

export default function CartPage({ userId, onCartChange }) {
  const { loading, data, error, reload } = useAsync(
    () => getCartDetail(userId),
    [userId]
  )

  const [actionError, setActionError] = useState(null)

  async function run(action) {
    setActionError(null)
    try {
      await action()
      reload()
      if (onCartChange) await onCartChange()
    } catch (err) {
      setActionError(err)
    }
  }

  return (
    <section>
      <h1 style={{ marginTop: '2rem' }}>Keranjang Belanja</h1>

      {actionError && (
        <p className="alert alert-error">
          {describeError(actionError)}
        </p>
      )}

      <AsyncView loading={loading} error={error} onRetry={reload}>
        {/* Empty state */}
        {data && data.items.length === 0 && (
          <div className="cart-empty">
            <span className="cart-empty-icon">🛒</span>
            <p>Keranjang kamu kosong. Yuk, mulai belanja!</p>
            <Link to="/">
              <button type="button">Mulai Belanja</button>
            </Link>
          </div>
        )}

        {/* Cart with items */}
        {data && data.items.length > 0 && (
          <div className="cart-layout">
            {/* Left: table */}
            <div>
              <table>
                <thead>
                  <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th></th>
                  </tr>
                </thead>

                <tbody>
                  {data.items.map((item) => (
                    <tr key={item.id}>
                      <td>
                        {item.product
                          ? item.product.name
                          : <em>Produk tidak tersedia</em>}
                      </td>

                      <td>
                        {item.product
                          ? formatRupiah(item.product.price)
                          : '-'}
                      </td>

                      <td>
                        <button
                          type="button"
                          aria-label="Kurangi"
                          disabled={item.quantity <= 1}
                          onClick={() =>
                            run(() =>
                              updateCartItem(item.id, item.quantity - 1)
                            )
                          }
                        >
                          −
                        </button>

                        <span className="qty">{item.quantity}</span>

                        <button
                          type="button"
                          aria-label="Tambah"
                          onClick={() =>
                            run(() =>
                              updateCartItem(item.id, item.quantity + 1)
                            )
                          }
                        >
                          +
                        </button>
                      </td>

                      <td>
                        {item.product
                          ? formatRupiah(
                              Number(item.product.price) * item.quantity
                            )
                          : '-'}
                      </td>

                      <td>
                        <button
                          type="button"
                          className="btn-danger"
                          onClick={() =>
                            run(() => removeCartItem(item.id))
                          }
                        >
                          Hapus
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>

              <div className="cart-actions">
                <button
                  type="button"
                  className="btn-secondary"
                  onClick={() => run(() => clearCart(userId))}
                >
                  🗑 Kosongkan Keranjang
                </button>
              </div>
            </div>

          </div>
        )}
      </AsyncView>
    </section>
  )
}
