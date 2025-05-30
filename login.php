<?php
include 'config.php';
session_start();

// Initialize message array
$message = [];

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']); // Note: MD5 is not secure, consider using password_hash()

    // Use user_info table instead of user_form
    $select_users = $conn->prepare("SELECT * FROM user_info WHERE email = ? AND password = ?");
    $select_users->bind_param("ss", $email, $password);
    $select_users->execute();
    $result = $select_users->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        header('location:index.php');
        exit();
    } else {
        $message[] = 'Incorrect email or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Welcome Back - Login</title>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }

      body {
         font-family: 'Inter', sans-serif;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         min-height: 100vh;
         display: flex;
         align-items: center;
         justify-content: center;
         position: relative;
         overflow: hidden;
      }

      /* Animated background elements */
      body::before {
         content: '';
         position: absolute;
         top: -50%;
         left: -50%;
         width: 200%;
         height: 200%;
         background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
         background-size: 50px 50px;
         animation: float 20s ease-in-out infinite;
         z-index: 1;
      }

      @keyframes float {
         0%, 100% { transform: translateY(0px) rotate(0deg); }
         50% { transform: translateY(-20px) rotate(180deg); }
      }

      /* Floating orbs */
      .orb {
         position: absolute;
         border-radius: 50%;
         background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
         backdrop-filter: blur(10px);
         animation: orbit 15s linear infinite;
         z-index: 1;
      }

      .orb:nth-child(1) {
         width: 80px;
         height: 80px;
         top: 20%;
         left: 10%;
         animation-delay: 0s;
      }

      .orb:nth-child(2) {
         width: 60px;
         height: 60px;
         top: 60%;
         right: 15%;
         animation-delay: -5s;
      }

      .orb:nth-child(3) {
         width: 100px;
         height: 100px;
         bottom: 20%;
         left: 20%;
         animation-delay: -10s;
      }

      @keyframes orbit {
         0% { transform: translateY(0px) scale(1); }
         50% { transform: translateY(-30px) scale(1.1); }
         100% { transform: translateY(0px) scale(1); }
      }

      .login-container {
         position: relative;
         z-index: 10;
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(20px);
         border-radius: 24px;
         padding: 3rem;
         width: 100%;
         max-width: 440px;
         box-shadow: 
            0 32px 64px rgba(31, 38, 135, 0.37),
            0 8px 32px rgba(31, 38, 135, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.3);
         border: 1px solid rgba(255, 255, 255, 0.18);
         animation: slideUp 0.8s ease-out;
         margin: 2rem;
      }

      @keyframes slideUp {
         from {
            opacity: 0;
            transform: translateY(30px);
         }
         to {
            opacity: 1;
            transform: translateY(0);
         }
      }

      .welcome-section {
         text-align: center;
         margin-bottom: 2.5rem;
      }

      .welcome-section h1 {
         font-size: 2.25rem;
         font-weight: 700;
         background: linear-gradient(135deg, #667eea, #764ba2);
         background-clip: text;
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         margin-bottom: 0.5rem;
         letter-spacing: -0.025em;
      }

      .welcome-section p {
         color: #64748b;
         font-size: 1rem;
         font-weight: 400;
      }

      .form-group {
         position: relative;
         margin-bottom: 1.5rem;
      }

      .form-group i {
         position: absolute;
         left: 1rem;
         top: 50%;
         transform: translateY(-50%);
         color: #94a3b8;
         font-size: 1.1rem;
         z-index: 2;
         transition: all 0.3s ease;
      }

      .form-input {
         width: 100%;
         padding: 1rem 1rem 1rem 3rem;
         border: 2px solid #e2e8f0;
         border-radius: 12px;
         font-size: 1rem;
         font-weight: 400;
         background: rgba(255, 255, 255, 0.9);
         transition: all 0.3s ease;
         outline: none;
         color: #1e293b;
      }

      .form-input:focus {
         border-color: #667eea;
         background: rgba(255, 255, 255, 1);
         box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
         transform: translateY(-2px);
      }

      .form-input:focus + i {
         color: #667eea;
         transform: translateY(-50%) scale(1.1);
      }

      .form-input::placeholder {
         color: #94a3b8;
         font-weight: 400;
      }

      .login-btn {
         width: 100%;
         padding: 1rem;
         background: linear-gradient(135deg, #667eea, #764ba2);
         color: white;
         border: none;
         border-radius: 12px;
         font-size: 1.1rem;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s ease;
         margin-bottom: 1.5rem;
         position: relative;
         overflow: hidden;
      }

      .login-btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.5s;
      }

      .login-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 12px 24px rgba(102, 126, 234, 0.4);
      }

      .login-btn:hover::before {
         left: 100%;
      }

      .login-btn:active {
         transform: translateY(0);
      }

      .register-link {
         text-align: center;
         margin-top: 1.5rem;
         padding-top: 1.5rem;
         border-top: 1px solid #e2e8f0;
      }

      .register-link p {
         color: #64748b;
         font-size: 0.95rem;
      }

      .register-link a {
         color: #667eea;
         text-decoration: none;
         font-weight: 600;
         transition: all 0.3s ease;
         position: relative;
      }

      .register-link a::after {
         content: '';
         position: absolute;
         width: 0;
         height: 2px;
         bottom: -2px;
         left: 50%;
         background: linear-gradient(135deg, #667eea, #764ba2);
         transition: all 0.3s ease;
         transform: translateX(-50%);
      }

      .register-link a:hover::after {
         width: 100%;
      }

      .register-link a:hover {
         color: #764ba2;
      }

      /* Enhanced message styling */
      .message {
         position: fixed;
         top: 2rem;
         right: 2rem;
         background: linear-gradient(135deg, #ef4444, #dc2626);
         color: white;
         padding: 1rem 1.5rem;
         border-radius: 12px;
         box-shadow: 0 8px 32px rgba(239, 68, 68, 0.3);
         cursor: pointer;
         z-index: 1000;
         font-weight: 500;
         animation: slideInRight 0.5s ease-out;
         backdrop-filter: blur(10px);
         border: 1px solid rgba(255, 255, 255, 0.1);
      }

      .message::before {
         content: '⚠';
         margin-right: 0.5rem;
         font-size: 1.1rem;
      }

      @keyframes slideInRight {
         from {
            opacity: 0;
            transform: translateX(100px);
         }
         to {
            opacity: 1;
            transform: translateX(0);
         }
      }

      .message:hover {
         transform: translateX(-5px);
         box-shadow: 0 12px 40px rgba(239, 68, 68, 0.4);
      }

      /* Responsive design */
      @media (max-width: 480px) {
         .login-container {
            margin: 1rem;
            padding: 2rem;
         }

         .welcome-section h1 {
            font-size: 1.875rem;
         }

         .message {
            top: 1rem;
            right: 1rem;
            left: 1rem;
            right: 1rem;
         }
      }

      /* Loading animation for form submission */
      .loading .login-btn {
         pointer-events: none;
         background: #94a3b8;
      }

      .loading .login-btn::after {
         content: '';
         position: absolute;
         width: 20px;
         height: 20px;
         border: 2px solid #ffffff;
         border-top: 2px solid transparent;
         border-radius: 50%;
         animation: spin 1s linear infinite;
         left: 50%;
         top: 50%;
         transform: translate(-50%, -50%);
      }

      @keyframes spin {
         0% { transform: translate(-50%, -50%) rotate(0deg); }
         100% { transform: translate(-50%, -50%) rotate(360deg); }
      }
   </style>
</head>
<body>
   <!-- Floating orbs for ambiance -->
   <div class="orb"></div>
   <div class="orb"></div>
   <div class="orb"></div>

   <?php
   if (isset($message)) {
       foreach ($message as $msg) {
           echo "<div class='message' onclick='this.remove();'>$msg</div>";
       }
   }
   ?>

   <div class="login-container">
      <div class="welcome-section">
         <h1>Welcome Back</h1>
         <p>Sign in to continue your journey</p>
      </div>

      <form action="" method="post" id="loginForm">
         <div class="form-group">
            <input type="email" name="email" required placeholder="Enter your email address" class="form-input">
            <i class="fas fa-envelope"></i>
         </div>

         <div class="form-group">
            <input type="password" name="password" required placeholder="Enter your password" class="form-input">
            <i class="fas fa-lock"></i>
         </div>

         <button type="submit" name="submit" class="login-btn">
            Sign In
         </button>
      </form>

      <div class="register-link">
         <p>Don't have an account? <a href="register.php">Create one now</a></p>
      </div>
   </div>

   <script>
      // Add loading state on form submission
      document.getElementById('loginForm').addEventListener('submit', function() {
         document.body.classList.add('loading');
      });

      // Add subtle parallax effect to orbs
      document.addEventListener('mousemove', function(e) {
         const orbs = document.querySelectorAll('.orb');
         const x = (e.clientX / window.innerWidth) * 100;
         const y = (e.clientY / window.innerHeight) * 100;

         orbs.forEach((orb, index) => {
            const speed = (index + 1) * 0.5;
            orb.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
         });
      });

      // Auto-hide messages after 5 seconds
      setTimeout(function() {
         const messages = document.querySelectorAll('.message');
         messages.forEach(function(message) {
            message.style.animation = 'slideInRight 0.5s ease-out reverse';
            setTimeout(function() {
               message.remove();
            }, 500);
         });
      }, 5000);
   </script>
</body>
</html>