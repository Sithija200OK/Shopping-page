<?php
include 'config.php';

// Initialize message array
$message = [];

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']); // Note: MD5 is not secure, consider using password_hash()
    $cpassword = md5($_POST['cpassword']);

    // Check if email already exists in user_info table (changed from user_form)
    $select_users = $conn->prepare("SELECT * FROM user_info WHERE email = ?");
    $select_users->bind_param("s", $email);
    $select_users->execute();
    $result = $select_users->get_result();

    if ($result->num_rows > 0) {
        $message[] = 'User already exists!';
    } else {
        if ($password != $cpassword) {
            $message[] = 'Passwords do not match!';
        } else {
            // Insert into user_info table (changed from user_form)
            $insert_user = $conn->prepare("INSERT INTO user_info (name, email, password) VALUES (?, ?, ?)");
            $insert_user->bind_param("sss", $name, $email, $password);
            
            if ($insert_user->execute()) {
                $message[] = 'Registered successfully!';
                header('location:login.php');
                exit();
            } else {
                $message[] = 'Registration failed: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Create Account - Register</title>
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
         background-size: 60px 60px;
         animation: float 25s ease-in-out infinite;
         z-index: 1;
      }

      @keyframes float {
         0%, 100% { transform: translateY(0px) rotate(0deg); }
         50% { transform: translateY(-25px) rotate(180deg); }
      }

      /* Enhanced floating elements for register */
      .floating-element {
         position: absolute;
         border-radius: 50%;
         background: linear-gradient(45deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05));
         backdrop-filter: blur(15px);
         animation: floatAround 20s linear infinite;
         z-index: 1;
      }

      .floating-element:nth-child(1) {
         width: 120px;
         height: 120px;
         top: 15%;
         left: 8%;
         animation-delay: 0s;
      }

      .floating-element:nth-child(2) {
         width: 80px;
         height: 80px;
         top: 70%;
         right: 12%;
         animation-delay: -7s;
      }

      .floating-element:nth-child(3) {
         width: 100px;
         height: 100px;
         bottom: 25%;
         left: 15%;
         animation-delay: -14s;
      }

      .floating-element:nth-child(4) {
         width: 60px;
         height: 60px;
         top: 30%;
         right: 25%;
         animation-delay: -3s;
      }

      @keyframes floatAround {
         0% { transform: translateY(0px) translateX(0px) scale(1); }
         25% { transform: translateY(-20px) translateX(10px) scale(1.05); }
         50% { transform: translateY(-10px) translateX(-15px) scale(0.95); }
         75% { transform: translateY(-30px) translateX(5px) scale(1.1); }
         100% { transform: translateY(0px) translateX(0px) scale(1); }
      }

      .register-container {
         position: relative;
         z-index: 10;
         background: rgba(255, 255, 255, 0.95);
         backdrop-filter: blur(20px);
         border-radius: 24px;
         padding: 3rem;
         width: 100%;
         max-width: 480px;
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
            transform: translateY(40px) scale(0.95);
         }
         to {
            opacity: 1;
            transform: translateY(0) scale(1);
         }
      }

      .welcome-section {
         text-align: center;
         margin-bottom: 2.5rem;
      }

      .welcome-section h1 {
         font-size: 2.5rem;
         font-weight: 700;
         background: linear-gradient(135deg, #667eea, #764ba2);
         background-clip: text;
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         margin-bottom: 0.5rem;
         letter-spacing: -0.025em;
         animation: titleGlow 2s ease-in-out infinite alternate;
      }

      @keyframes titleGlow {
         from { filter: brightness(1); }
         to { filter: brightness(1.1); }
      }

      .welcome-section p {
         color: #64748b;
         font-size: 1rem;
         font-weight: 400;
         line-height: 1.5;
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
         transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }

      .form-input {
         width: 100%;
         padding: 1.1rem 1rem 1.1rem 3rem;
         border: 2px solid #e2e8f0;
         border-radius: 14px;
         font-size: 1rem;
         font-weight: 400;
         background: rgba(255, 255, 255, 0.9);
         transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
         outline: none;
         color: #1e293b;
      }

      .form-input:focus {
         border-color: #667eea;
         background: rgba(255, 255, 255, 1);
         box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
         transform: translateY(-3px);
      }

      .form-input:focus + i {
         color: #667eea;
         transform: translateY(-50%) scale(1.15) rotate(5deg);
      }

      .form-input::placeholder {
         color: #94a3b8;
         font-weight: 400;
      }

      /* Password strength indicator */
      .password-strength {
         margin-top: 0.5rem;
         height: 3px;
         background: #e2e8f0;
         border-radius: 2px;
         overflow: hidden;
         opacity: 0;
         transition: opacity 0.3s ease;
      }

      .password-strength.active {
         opacity: 1;
      }

      .strength-bar {
         height: 100%;
         width: 0%;
         background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981);
         transition: width 0.3s ease;
         border-radius: 2px;
      }

      .register-btn {
         width: 100%;
         padding: 1.1rem;
         background: linear-gradient(135deg, #667eea, #764ba2);
         color: white;
         border: none;
         border-radius: 14px;
         font-size: 1.1rem;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
         margin-bottom: 1.5rem;
         position: relative;
         overflow: hidden;
      }

      .register-btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.6s ease;
      }

      .register-btn:hover {
         transform: translateY(-3px);
         box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
      }

      .register-btn:hover::before {
         left: 100%;
      }

      .register-btn:active {
         transform: translateY(-1px);
      }

      .login-link {
         text-align: center;
         margin-top: 1.5rem;
         padding-top: 1.5rem;
         border-top: 1px solid #e2e8f0;
      }

      .login-link p {
         color: #64748b;
         font-size: 0.95rem;
      }

      .login-link a {
         color: #667eea;
         text-decoration: none;
         font-weight: 600;
         transition: all 0.3s ease;
         position: relative;
      }

      .login-link a::after {
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

      .login-link a:hover::after {
         width: 100%;
      }

      .login-link a:hover {
         color: #764ba2;
         transform: translateY(-1px);
      }

      /* Enhanced message styling */
      .message {
         position: fixed;
         top: 2rem;
         right: 2rem;
         padding: 1rem 1.5rem;
         border-radius: 14px;
         cursor: pointer;
         z-index: 1000;
         font-weight: 500;
         backdrop-filter: blur(10px);
         border: 1px solid rgba(255, 255, 255, 0.1);
         animation: slideInRight 0.5s cubic-bezier(0.4, 0, 0.2, 1);
         max-width: 350px;
      }

      .message.error {
         background: linear-gradient(135deg, #ef4444, #dc2626);
         color: white;
         box-shadow: 0 8px 32px rgba(239, 68, 68, 0.3);
      }

      .message.success {
         background: linear-gradient(135deg, #10b981, #059669);
         color: white;
         box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
      }

      .message.error::before {
         content: '⚠';
         margin-right: 0.5rem;
         font-size: 1.1rem;
      }

      .message.success::before {
         content: '✓';
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
         transform: translateX(-8px);
      }

      /* Form validation indicators */
      .form-input.valid {
         border-color: #10b981;
      }

      .form-input.invalid {
         border-color: #ef4444;
      }

      .form-input.valid + i {
         color: #10b981;
      }

      .form-input.invalid + i {
         color: #ef4444;
      }

      /* Responsive design */
      @media (max-width: 480px) {
         .register-container {
            margin: 1rem;
            padding: 2rem;
         }

         .welcome-section h1 {
            font-size: 2rem;
         }

         .message {
            top: 1rem;
            right: 1rem;
            left: 1rem;
         }
      }

      /* Loading state */
      .loading .register-btn {
         pointer-events: none;
         background: #94a3b8;
      }

      .loading .register-btn::after {
         content: '';
         position: absolute;
         width: 22px;
         height: 22px;
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

      /* Success checkmark animation */
      .success-checkmark {
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         width: 50px;
         height: 50px;
         border-radius: 50%;
         background: #10b981;
         opacity: 0;
         animation: successPop 0.6s ease-out;
      }

      .success-checkmark::after {
         content: '✓';
         color: white;
         font-size: 24px;
         font-weight: bold;
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
      }

      @keyframes successPop {
         0% {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0);
         }
         50% {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.2);
         }
         100% {
            opacity: 0;
            transform: translate(-50%, -50%) scale(1);
         }
      }
   </style>
</head>
<body>
   <!-- Enhanced floating elements -->
   <div class="floating-element"></div>
   <div class="floating-element"></div>
   <div class="floating-element"></div>
   <div class="floating-element"></div>

   <?php
   if (isset($message)) {
       foreach ($message as $msg) {
           $messageClass = (strpos($msg, 'successfully') !== false) ? 'success' : 'error';
           echo "<div class='message $messageClass' onclick='this.remove();'>$msg</div>";
       }
   }
   ?>

   <div class="register-container">
      <div class="welcome-section">
         <h1>Join Us Today</h1>
         <p>Create your account and start your amazing journey with us</p>
      </div>

      <form action="" method="post" id="registerForm">
         <div class="form-group">
            <input type="text" name="name" required placeholder="Enter your full name" class="form-input" id="nameInput">
            <i class="fas fa-user"></i>
         </div>

         <div class="form-group">
            <input type="email" name="email" required placeholder="Enter your email address" class="form-input" id="emailInput">
            <i class="fas fa-envelope"></i>
         </div>

         <div class="form-group">
            <input type="password" name="password" required placeholder="Create a strong password" class="form-input" id="passwordInput">
            <i class="fas fa-lock"></i>
            <div class="password-strength" id="passwordStrength">
               <div class="strength-bar" id="strengthBar"></div>
            </div>
         </div>

         <div class="form-group">
            <input type="password" name="cpassword" required placeholder="Confirm your password" class="form-input" id="confirmPasswordInput">
            <i class="fas fa-lock"></i>
         </div>

         <button type="submit" name="submit" class="register-btn">
            Create Account
         </button>
      </form>

      <div class="login-link">
         <p>Already have an account? <a href="login.php">Sign in here</a></p>
      </div>
   </div>

   <script>
      // Enhanced form validation and interactions
      const form = document.getElementById('registerForm');
      const nameInput = document.getElementById('nameInput');
      const emailInput = document.getElementById('emailInput');
      const passwordInput = document.getElementById('passwordInput');
      const confirmPasswordInput = document.getElementById('confirmPasswordInput');
      const passwordStrength = document.getElementById('passwordStrength');
      const strengthBar = document.getElementById('strengthBar');

      // Password strength indicator
      passwordInput.addEventListener('input', function() {
         const password = this.value;
         const strength = calculatePasswordStrength(password);
         
         passwordStrength.classList.add('active');
         strengthBar.style.width = strength + '%';
         
         if (strength < 30) {
            strengthBar.style.background = '#ef4444';
         } else if (strength < 70) {
            strengthBar.style.background = '#f59e0b';
         } else {
            strengthBar.style.background = '#10b981';
         }
      });

      function calculatePasswordStrength(password) {
         let strength = 0;
         
         // Length
         if (password.length >= 8) strength += 25;
         if (password.length >= 12) strength += 15;
         
         // Character types
         if (/[a-z]/.test(password)) strength += 15;
         if (/[A-Z]/.test(password)) strength += 15;
         if (/[0-9]/.test(password)) strength += 15;
         if (/[^A-Za-z0-9]/.test(password)) strength += 15;
         
         return Math.min(strength, 100);
      }

      // Real-time validation
      function validateField(input, validationFn) {
         input.addEventListener('blur', function() {
            if (validationFn(this.value)) {
               this.classList.add('valid');
               this.classList.remove('invalid');
            } else {
               this.classList.add('invalid');
               this.classList.remove('valid');
            }
         });
      }

      validateField(nameInput, value => value.trim().length >= 2);
      validateField(emailInput, value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value));
      validateField(passwordInput, value => value.length >= 6);

      // Confirm password validation
      confirmPasswordInput.addEventListener('blur', function() {
         if (this.value === passwordInput.value && this.value.length > 0) {
            this.classList.add('valid');
            this.classList.remove('invalid');
         } else {
            this.classList.add('invalid');
            this.classList.remove('valid');
         }
      });

      // Form submission with loading state
      form.addEventListener('submit', function(e) {
         document.body.classList.add('loading');
         
         // Show success animation if validation passes
         setTimeout(() => {
            const successMark = document.createElement('div');
            successMark.className = 'success-checkmark';
            document.querySelector('.register-container').appendChild(successMark);
         }, 500);
      });

      // Enhanced parallax effect for floating elements
      document.addEventListener('mousemove', function(e) {
         const elements = document.querySelectorAll('.floating-element');
         const x = (e.clientX / window.innerWidth) * 100;
         const y = (e.clientY / window.innerHeight) * 100;

         elements.forEach((element, index) => {
            const speed = (index + 1) * 0.3;
            const rotateSpeed = (index + 1) * 0.1;
            element.style.transform = `translate(${x * speed}px, ${y * speed}px) rotate(${x * rotateSpeed}deg)`;
         });
      });

      // Auto-hide messages with enhanced animation
      setTimeout(function() {
         const messages = document.querySelectorAll('.message');
         messages.forEach(function(message) {
            message.style.animation = 'slideInRight 0.5s ease-out reverse';
            setTimeout(function() {
               message.remove();
            }, 500);
         });
      }, 6000);

      // Add subtle shake animation for invalid inputs
      document.querySelectorAll('.form-input').forEach(input => {
         input.addEventListener('invalid', function() {
            this.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
               this.style.animation = '';
            }, 500);
         });
      });

      // CSS for shake animation
      const shakeKeyframes = `
         @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
         }
      `;
      
      const styleSheet = document.createElement('style');
      styleSheet.textContent = shakeKeyframes;
      document.head.appendChild(styleSheet);
   </script>
</body>
</html>