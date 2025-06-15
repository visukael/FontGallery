<?php
session_start();
if (isset($_SESSION['user'])) {
  header("Location: landingPage.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-20px) translateX(-50%);
      }

      to {
        opacity: 1;
        transform: translateY(0) translateX(-50%);
      }
    }

    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translateY(-20px) translateX(-50%);
      }
    }

    .animate-slideIn {
      animation: slideIn 0.4s ease-out forwards;
    }

    .fadeOut {
      animation: fadeOut 0.4s ease-in forwards;
    }
  </style>
</head>

<body class="bg-black text-white font-sans h-screen overflow-hidden">

  <?php if (isset($_GET['success']) || isset($_GET['error'])): ?>
    <div class="fixed top-6 left-1/2 transform -translate-x-1/2 bg-white/5 text-white text-sm px-5 py-3 rounded-lg border border-white/10 shadow-lg backdrop-blur-lg z-50 animate-slideIn">
      <?php
      if (($_GET['success'] ?? '') === 'created') echo "🎉 <strong>Account created!</strong> You can now log in.";
      if (($_GET['error'] ?? '') === 'exists') echo "⚠️ <strong>Username or email already exists.</strong>";
      if (($_GET['error'] ?? '') === 'failed') echo "❌ <strong>Failed to create account.</strong> Try again.";
      if (($_GET['error'] ?? '') === 'invalidpass') echo "❌ <strong>Incorrect password.</strong>";
      if (($_GET['error'] ?? '') === 'nouser') echo "⚠️ <strong>User not found.</strong> Make sure you’re registered.";
      if (($_GET['error'] ?? '') === 'unauthorized') echo "⚠️ <strong>Please Login First.</strong> Make sure you’re registered.";
      ?>
    </div>
    <script>
      setTimeout(() => {
        const notif = document.querySelector('.animate-slideIn');
        if (notif) {
          notif.classList.remove('animate-slideIn');
          notif.classList.add('fadeOut');
          setTimeout(() => notif.remove(), 400);
        }
      }, 4000);
    </script>
  <?php endif; ?>

  <div class="flex h-full">
    <!-- Left Form Panel -->
    <div class="w-full md:w-1/2 bg-[#0d0d0d] flex items-center justify-center p-10">
      <div class="w-full max-w-sm space-y-8">
        <div class="text-center">
          <h1 class="text-3xl font-bold">Welcome</h1>
          <p class="text-sm text-neutral-400">Sign in or create an account to continue</p>
        </div>

        <form action="../Controller/auth.php" method="POST" class="space-y-4">
          <input type="hidden" name="action" id="formAction" value="login" />

          <input type="text" name="username" required placeholder="Username"
            class="w-full px-4 py-3 bg-[#1a1a1a] border border-neutral-700 rounded-md text-white placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />

          <input type="password" name="password" required placeholder="Password"
            class="w-full px-4 py-3 bg-[#1a1a1a] border border-neutral-700 rounded-md text-white placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />

          <div id="extraFields" class="hidden space-y-4">
            <input type="email" name="email" placeholder="Email"
              class="w-full px-4 py-3 bg-[#1a1a1a] border border-neutral-700 rounded-md text-white placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />

            <input type="text" name="full_name" placeholder="Full Name"
              class="w-full px-4 py-3 bg-[#1a1a1a] border border-neutral-700 rounded-md text-white placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>

          <button type="submit"
            class="w-full py-3 rounded-md bg-white text-black font-medium hover:bg-neutral-200 transition">
            Continue
          </button>
        </form>

        <p class="text-center text-sm text-neutral-400">
          <span id="toggleText">Don't have an account?</span>
          <button id="toggleMode" class="text-white underline">Sign up</button>
        </p>

        <a href="landingPage.php"
          class="block text-center text-neutral-500 hover:text-white underline text-sm mt-2 transition">
          Continue without an account
        </a>
      </div>
    </div>

    <!-- Right Image Panel -->
    <div class="hidden md:block w-1/2 relative bg-cover bg-center"
      style="background-image: url('../Assets/login.png');">
      <div class="absolute inset-0 bg-black/50"></div>
    </div>
  </div>

  <script>
    const toggle = document.getElementById('toggleMode');
    const formAction = document.getElementById('formAction');
    const extraFields = document.getElementById('extraFields');
    const toggleText = document.getElementById('toggleText');

    let isSignUp = false;
    toggle.addEventListener('click', () => {
      isSignUp = !isSignUp;
      formAction.value = isSignUp ? 'signup' : 'login';
      extraFields.classList.toggle('hidden', !isSignUp);
      toggle.textContent = isSignUp ? 'Log in' : 'Sign up';
      toggleText.textContent = isSignUp ? 'Already have an account?' : "Don't have an account?";
    });
  </script>

</body>

</html>