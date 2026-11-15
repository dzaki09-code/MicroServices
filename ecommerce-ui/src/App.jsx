// src/App.jsx

// Kerangka aplikasi: navigasi, pemilih user,
// dan peta halaman (routes).

import { useCallback, useEffect, useState } from 'react'
import { Route, Routes } from 'react-router-dom'
import { getCart } from './api/cartApi'
import Footer from './components/Footer'
import Navbar from './components/Navbar'
import CartPage from './pages/CartPage'
import ProductDetailPage from './pages/ProductDetailPage'
import ProductListPage from './pages/ProductListPage'

export default function App() {
  // User aktif disimpan di localStorage supaya tidak kembali ke 1
  // saat halaman dimuat ulang.
  const [userId, setUserId] = useState(
    () => Number(localStorage.getItem('userId')) || 1
  )

  // Keep the badge synchronized with the cart service.
  const [cartCount, setCartCount] = useState(0)

  const refreshCartCount = useCallback(async () => {
    try {
      const cart = await getCart(userId)
      setCartCount(cart.items.length)
    } catch (error) {
      console.error('Gagal memuat jumlah item keranjang:', error)
    }
  }, [userId])

  useEffect(() => {
    let active = true

    getCart(userId)
      .then((cart) => {
        if (active) setCartCount(cart.items.length)
      })
      .catch((error) => {
        if (active) {
          console.error('Gagal memuat jumlah item keranjang:', error)
        }
      })

    return () => {
      active = false
    }
  }, [userId])

  function changeUser(value) {
    setUserId(value)
    localStorage.setItem('userId', String(value))
  }

  return (
    <>
      <Navbar userId={userId} onUserChange={changeUser} cartCount={cartCount} />

      <main>
        <Routes>
          <Route
            path="/"
            element={<ProductListPage />}
          />

          <Route
            path="/products/:id"
            element={
              <ProductDetailPage
                userId={userId}
                onCartChange={refreshCartCount}
              />
            }
          />

          <Route
            path="/cart"
            element={
              <CartPage userId={userId} onCartChange={refreshCartCount} />
            }
          />

          <Route
            path="*"
            element={
              <section>
                <p style={{ marginTop: '3rem' }}>Halaman tidak ditemukan.</p>
              </section>
            }
          />
        </Routes>
      </main>

      <Footer />
    </>
  )
}
