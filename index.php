<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('location:login.php');
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('location:login.php');
    exit;
}

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['product_quantity'];

    // Use prepared statements to prevent SQL injection
    $select_cart = $conn->prepare("SELECT * FROM cart WHERE name=? AND user_id=?");
    $select_cart->bind_param("si", $product_name, $user_id);
    $select_cart->execute();
    $result = $select_cart->get_result();

    if ($result->num_rows > 0) {
        $message[] = 'Product already added to cart!';
    } else {
        $insert_cart = $conn->prepare("INSERT INTO cart(user_id, name, price, image, quantity) VALUES(?, ?, ?, ?, ?)");
        $insert_cart->bind_param("isssi", $user_id, $product_name, $product_price, $product_image, $product_quantity);
        $insert_cart->execute();
        $message[] = 'Product added to cart!';
    }
}

// Update cart
if (isset($_POST['update_cart'])) {
    $update_quantity = $_POST['cart_quantity'];
    $update_id = $_POST['cart_id'];
    
    $update_cart = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update_cart->bind_param("ii", $update_quantity, $update_id);
    $update_cart->execute();
    
    $message[] = 'Cart updated successfully!';
}

// Remove item
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    
    $remove_item = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $remove_item->bind_param("i", $remove_id);
    $remove_item->execute();
    
    header('location:index.php');
    exit;
}

// Delete all
if (isset($_GET['delete_all'])) {
    $delete_all = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $delete_all->bind_param("i", $user_id);
    $delete_all->execute();
    
    header('location:index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Premium Shopping Experience</title>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }

      body {
         font-family: 'Inter', sans-serif;
         background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
         min-height: 100vh;
         color: #1e293b;
         line-height: 1.6;
      }

      /* Header */
      .header {
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(20px);
         border-bottom: 1px solid rgba(226, 232, 240, 0.5);
         position: fixed;
         top: 0;
         left: 0;
         right: 0;
         z-index: 1000;
         padding: 1rem 0;
         animation: slideDown 0.6s ease-out;
      }

      @keyframes slideDown {
         from { transform: translateY(-100%); opacity: 0; }
         to { transform: translateY(0); opacity: 1; }
      }

      .nav-container {
         max-width: 1400px;
         margin: 0 auto;
         padding: 0 2rem;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }

      .logo {
         font-size: 1.75rem;
         font-weight: 800;
         background: linear-gradient(135deg, #667eea, #764ba2);
         background-clip: text;
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }

      .user-info {
         display: flex;
         align-items: center;
         gap: 1.5rem;
      }

      .user-details {
         text-align: right;
      }

      .user-details .name {
         font-weight: 600;
         color: #1e293b;
         margin-bottom: 0.25rem;
      }

      .user-details .email {
         font-size: 0.875rem;
         color: #64748b;
      }

      .nav-actions {
         display: flex;
         gap: 0.75rem;
      }

      .nav-btn {
         padding: 0.5rem 1rem;
         border-radius: 8px;
         text-decoration: none;
         font-weight: 500;
         font-size: 0.875rem;
         transition: all 0.3s ease;
         border: none;
         cursor: pointer;
      }

      .nav-btn.primary {
         background: linear-gradient(135deg, #667eea, #764ba2);
         color: white;
      }

      .nav-btn.secondary {
         background: #f1f5f9;
         color: #475569;
      }

      .nav-btn.danger {
         background: #fef2f2;
         color: #dc2626;
      }

      .nav-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }

      /* Container */
      .container {
         max-width: 1400px;
         margin: 0 auto;
         padding: 6rem 2rem 2rem;
      }

      /* Messages */
      .message {
         position: fixed;
         top: 6rem;
         right: 2rem;
         padding: 1rem 1.5rem;
         border-radius: 12px;
         cursor: pointer;
         z-index: 1000;
         font-weight: 500;
         backdrop-filter: blur(10px);
         animation: slideInRight 0.5s ease-out;
         max-width: 350px;
         box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      }

      .message:has-text('added') {
         background: linear-gradient(135deg, #10b981, #059669);
         color: white;
         border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .message:has-text('already') {
         background: linear-gradient(135deg, #f59e0b, #d97706);
         color: white;
         border: 1px solid rgba(255, 255, 255, 0.2);
      }

      @keyframes slideInRight {
         from { transform: translateX(100%); opacity: 0; }
         to { transform: translateX(0); opacity: 1; }
      }

      /* Section Headers */
      .section-header {
         margin-bottom: 2rem;
         text-align: center;
      }

      .section-title {
         font-size: 2.5rem;
         font-weight: 700;
         background: linear-gradient(135deg, #1e293b, #475569);
         background-clip: text;
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         margin-bottom: 0.5rem;
      }

      .section-subtitle {
         color: #64748b;
         font-size: 1.125rem;
      }

      /* Products Section */
      .products {
         margin-bottom: 4rem;
      }

      .product-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
         gap: 2rem;
         margin-bottom: 3rem;
      }

      .product-card {
         background: white;
         border-radius: 20px;
         padding: 1.5rem;
         box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
         transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
         border: 1px solid rgba(226, 232, 240, 0.5);
         position: relative;
         overflow: hidden;
      }

      .product-card::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         right: 0;
         height: 4px;
         background: linear-gradient(135deg, #667eea, #764ba2);
         transform: scaleX(0);
         transition: transform 0.3s ease;
      }

      .product-card:hover::before {
         transform: scaleX(1);
      }

      .product-card:hover {
         transform: translateY(-8px);
         box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      }

      .product-image {
         width: 100%;
         height: 200px;
         object-fit: cover;
         border-radius: 12px;
         margin-bottom: 1rem;
         transition: transform 0.3s ease;
      }

      .product-card:hover .product-image {
         transform: scale(1.05);
      }

      .product-name {
         font-size: 1.125rem;
         font-weight: 600;
         color: #1e293b;
         margin-bottom: 0.5rem;
      }

      .product-price {
         font-size: 1.25rem;
         font-weight: 700;
         color: #667eea;
         margin-bottom: 1rem;
      }

      .product-controls {
         display: flex;
         gap: 0.75rem;
         align-items: center;
      }

      .quantity-input {
         width: 60px;
         padding: 0.5rem;
         border: 2px solid #e2e8f0;
         border-radius: 8px;
         text-align: center;
         font-weight: 500;
         transition: all 0.3s ease;
      }

      .quantity-input:focus {
         outline: none;
         border-color: #667eea;
         box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }

      .add-to-cart-btn {
         flex: 1;
         padding: 0.75rem 1rem;
         background: linear-gradient(135deg, #667eea, #764ba2);
         color: white;
         border: none;
         border-radius: 10px;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s ease;
         position: relative;
         overflow: hidden;
      }

      .add-to-cart-btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.5s;
      }

      .add-to-cart-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
      }

      .add-to-cart-btn:hover::before {
         left: 100%;
      }

      /* Shopping Cart */
      .shopping-cart {
         background: white;
         border-radius: 20px;
         padding: 2rem;
         box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
         border: 1px solid rgba(226, 232, 240, 0.5);
      }

      .cart-table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 2rem;
      }

      .cart-table th {
         background: linear-gradient(135deg, #f8fafc, #f1f5f9);
         padding: 1rem;
         text-align: left;
         font-weight: 600;
         color: #475569;
         border-bottom: 2px solid #e2e8f0;
      }

      .cart-table td {
         padding: 1.5rem 1rem;
         border-bottom: 1px solid #f1f5f9;
         vertical-align: middle;
      }

      .cart-table tr:hover {
         background: #f8fafc;
      }

      .cart-item-image {
         width: 80px;
         height: 80px;
         object-fit: cover;
         border-radius: 10px;
      }

      .cart-item-name {
         font-weight: 600;
         color: #1e293b;
      }

      .cart-item-price {
         font-weight: 600;
         color: #667eea;
      }

      .cart-total {
         font-weight: 700;
         color: #059669;
      }

      .update-form {
         display: flex;
         gap: 0.5rem;
         align-items: center;
      }

      .update-btn {
         padding: 0.5rem 1rem;
         background: #f59e0b;
         color: white;
         border: none;
         border-radius: 6px;
         font-size: 0.875rem;
         font-weight: 500;
         cursor: pointer;
         transition: all 0.3s ease;
      }

      .update-btn:hover {
         background: #d97706;
         transform: translateY(-1px);
      }

      .remove-btn {
         padding: 0.5rem 1rem;
         background: #ef4444;
         color: white;
         text-decoration: none;
         border-radius: 6px;
         font-size: 0.875rem;
         font-weight: 500;
         transition: all 0.3s ease;
      }

      .remove-btn:hover {
         background: #dc2626;
         transform: translateY(-1px);
      }

      .grand-total-row {
         background: linear-gradient(135deg, #f8fafc, #f1f5f9);
         font-weight: 700;
         font-size: 1.125rem;
      }

      .grand-total-row td {
         padding: 1.5rem 1rem;
         border-top: 2px solid #e2e8f0;
      }

      .cart-actions {
         display: flex;
         justify-content: space-between;
         align-items: center;
         gap: 1rem;
         margin-top: 2rem;
         padding-top: 2rem;
         border-top: 1px solid #e2e8f0;
      }

      .checkout-btn {
         padding: 1rem 2rem;
         background: linear-gradient(135deg, #059669, #047857);
         color: white;
         text-decoration: none;
         border-radius: 12px;
         font-weight: 600;
         font-size: 1.125rem;
         transition: all 0.3s ease;
         position: relative;
         overflow: hidden;
      }

      .checkout-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
      }

      .checkout-btn.disabled {
         background: #94a3b8;
         cursor: not-allowed;
         transform: none;
         box-shadow: none;
      }

      .delete-all-btn {
         padding: 1rem 2rem;
         background: linear-gradient(135deg, #ef4444, #dc2626);
         color: white;
         text-decoration: none;
         border-radius: 12px;
         font-weight: 600;
         transition: all 0.3s ease;
      }

      .delete-all-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
      }

      .delete-all-btn.disabled {
         background: #94a3b8;
         cursor: not-allowed;
         transform: none;
         box-shadow: none;
      }

      .empty-cart {
         text-align: center;
         padding: 3rem;
         color: #64748b;
      }

      .empty-cart i {
         font-size: 4rem;
         margin-bottom: 1rem;
         color: #cbd5e1;
      }

      /* Responsive Design */
      @media (max-width: 768px) {
         .nav-container {
            flex-direction: column;
            gap: 1rem;
            padding: 0 1rem;
         }

         .user-info {
            width: 100%;
            justify-content: space-between;
         }

         .container {
            padding: 8rem 1rem 2rem;
         }

         .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
         }

         .section-title {
            font-size: 2rem;
         }

         .cart-table {
            font-size: 0.875rem;
         }

         .cart-table th,
         .cart-table td {
            padding: 0.75rem 0.5rem;
         }

         .cart-actions {
            flex-direction: column;
            gap: 1rem;
         }

         .checkout-btn,
         .delete-all-btn {
            width: 100%;
            text-align: center;
         }

         .message {
            top: 1rem;
            right: 1rem;
            left: 1rem;
         }
      }

      /* Animation for cart items */
      .cart-item {
         animation: fadeInUp 0.5s ease-out;
      }

      @keyframes fadeInUp {
         from {
            opacity: 0;
            transform: translateY(20px);
         }
         to {
            opacity: 1;
            transform: translateY(0);
         }
      }

      /* Loading states */
      .loading {
         pointer-events: none;
         opacity: 0.7;
      }

      /* Success animations */
      .success-animation {
         animation: successPulse 0.6s ease-out;
      }

      @keyframes successPulse {
         0% { transform: scale(1); }
         50% { transform: scale(1.05); }
         100% { transform: scale(1); }
      }
   </style>
</head>
<body>

   <!-- Header -->
   <header class="header">
      <div class="nav-container">
         <div class="logo">
            <i class="fas fa-shopping-bag"></i> ShopCraft
         </div>
         
         <div class="user-info">
            <?php
            $select_user = $conn->prepare("SELECT * FROM user_info WHERE id = ?");
            $select_user->bind_param("i", $user_id);
            $select_user->execute();
            $result_user = $select_user->get_result();
            
            if ($result_user->num_rows > 0) {
               $fetch_user = $result_user->fetch_assoc();
            ?>
            <div class="user-details">
               <div class="name">Welcome, <?= htmlspecialchars($fetch_user['name']) ?></div>
               <div class="email"><?= htmlspecialchars($fetch_user['email']) ?></div>
            </div>
            
            <div class="nav-actions">
               <a href="login.php" class="nav-btn secondary">
                  <i class="fas fa-sign-in-alt"></i> Login
               </a>
               <a href="register.php" class="nav-btn primary">
                  <i class="fas fa-user-plus"></i> Register
               </a>
               <a href="index.php?logout=1" onclick="return confirm('Are you sure you want to logout?');" class="nav-btn danger">
                  <i class="fas fa-sign-out-alt"></i> Logout
               </a>
            </div>
            <?php } ?>
         </div>
      </div>
   </header>

   <!-- Messages -->
   <?php
   if (isset($message)) {
       foreach ($message as $msg) {
           echo "<div class='message' onclick='this.remove();'>$msg</div>";
       }
   }
   ?>

   <div class="container">
      <!-- Products Section -->
      <section class="products">
         <div class="section-header">
            <h1 class="section-title">Premium Collection</h1>
            <p class="section-subtitle">Discover our handpicked selection of exceptional products</p>
         </div>

         <div class="product-grid">
            <?php
            $select_product = $conn->query("SELECT * FROM products");
            if ($select_product->num_rows > 0) {
                while ($fetch_product = $select_product->fetch_assoc()) {
            ?>
            <form method="post" class="product-card" action="">
               <img src="images/<?= htmlspecialchars($fetch_product['image']) ?>" 
                    alt="<?= htmlspecialchars($fetch_product['name']) ?>" 
                    class="product-image">
               
               <div class="product-name"><?= htmlspecialchars($fetch_product['name']) ?></div>
               <div class="product-price">$<?= htmlspecialchars($fetch_product['price']) ?></div>
               
               <div class="product-controls">
                  <input type="number" min="1" name="product_quantity" value="1" class="quantity-input">
                  <input type="hidden" name="product_image" value="<?= htmlspecialchars($fetch_product['image']) ?>">
                  <input type="hidden" name="product_name" value="<?= htmlspecialchars($fetch_product['name']) ?>">
                  <input type="hidden" name="product_price" value="<?= htmlspecialchars($fetch_product['price']) ?>">
                  <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                     <i class="fas fa-cart-plus"></i> Add to Cart
                  </button>
               </div>
            </form>
            <?php 
                }
            } else {
                echo "<div class='empty-cart'><i class='fas fa-box-open'></i><p>No products available at the moment</p></div>";
            }
            ?>
         </div>
      </section>

      <!-- Shopping Cart Section -->
      <section class="shopping-cart">
         <div class="section-header">
            <h2 class="section-title">Your Shopping Cart</h2>
            <p class="section-subtitle">Review and manage your selected items</p>
         </div>

         <table class="cart-table">
            <thead>
               <tr>
                  <th>Product</th>
                  <th>Name</th>
                  <th>Price</th>
                  <th>Quantity</th>
                  <th>Total</th>
                  <th>Actions</th>
               </tr>
            </thead>
            <tbody>
               <?php
               $cart_query = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
               $cart_query->bind_param("i", $user_id);
               $cart_query->execute();
               $result_cart = $cart_query->get_result();
               
               $grand_total = 0;
               if ($result_cart->num_rows > 0) {
                   while ($item = $result_cart->fetch_assoc()) {
                       $total = $item['price'] * $item['quantity'];
                       $grand_total += $total;
               ?>
               <tr class="cart-item">
                  <td>
                     <img src="images/<?= htmlspecialchars($item['image']) ?>" 
                          alt="<?= htmlspecialchars($item['name']) ?>" 
                          class="cart-item-image">
                  </td>
                  <td class="cart-item-name"><?= htmlspecialchars($item['name']) ?></td>
                  <td class="cart-item-price">$<?= htmlspecialchars($item['price']) ?></td>
                  <td>
                     <form action="" method="post" class="update-form">
                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                        <input type="number" min="1" name="cart_quantity" value="<?= $item['quantity'] ?>" class="quantity-input">
                        <button type="submit" name="update_cart" class="update-btn">
                           <i class="fas fa-sync"></i> Update
                        </button>
                     </form>
                  </td>
                  <td class="cart-total">$<?= $total ?></td>
                  <td>
                     <a href="index.php?remove=<?= $item['id'] ?>" 
                        class="remove-btn" 
                        onclick="return confirm('Remove this item from cart?');">
                        <i class="fas fa-trash"></i> Remove
                     </a>
                  </td>
               </tr>
               <?php 
                   }
               } else { 
               ?>
               <tr>
                  <td colspan="6" class="empty-cart">
                     <i class="fas fa-shopping-cart"></i>
                     <p>Your cart is empty</p>
                     <small>Add some products to get started!</small>
                  </td>
               </tr>
               <?php } ?>
               
               <tr class="grand-total-row">
                  <td colspan="4"><strong>Grand Total:</strong></td>
                  <td><strong>$<?= $grand_total ?></strong></td>
                  <td>
                     <a href="index.php?delete_all" 
                        onclick="return confirm('Delete all items from cart?');" 
                        class="delete-all-btn <?= ($grand_total > 0) ? '' : 'disabled' ?>">
                        <i class="fas fa-trash-alt"></i> Clear Cart
                     </a>
                  </td>
               </tr>
            </tbody>
         </table>

         <div class="cart-actions">
            <div class="cart-summary">
               <span style="font-size: 1.25rem; font-weight: 600; color: #475569;">
                  Items in cart: <?= $result_cart->num_rows ?>
               </span>
            </div>
            <a href="#" class="checkout-btn <?= ($grand_total > 0) ? '' : 'disabled' ?>">
               <i class="fas fa-credit-card"></i> Proceed to Checkout ($<?= $grand_total ?>)
            </a>
         </div>
      </section>
   </div>

   <script>
      // Enhanced interactions
      document.addEventListener('DOMContentLoaded', function() {
         // Auto-hide messages
         setTimeout(function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
               message.style.animation = 'slideInRight 0.5s ease-out reverse';
               setTimeout(() => message.remove(), 500);
            });
         }, 4000);

         // Add to cart animation
         document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
               const card = this.closest('.product-card');
               card.classList.add('success-animation');
               setTimeout(() => card.classList.remove('success-animation'), 600);
            });
         });

         // Quantity input validation
         document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('input', function() {
               if (this.value < 1) this.value = 1;
               if (this.value > 999) this.value = 999;
            });
         });

         // Smooth scroll for checkout
         document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
               e.preventDefault();
               const target = document.querySelector(this.getAttribute('href'));
               if (target) {
                  target.scrollIntoView({ behavior: 'smooth' });
               }
            });
         });

         // Loading states for forms
         document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
               this.classList.add('loading');
            });
         });
      });
   </script>
</body>
</html>