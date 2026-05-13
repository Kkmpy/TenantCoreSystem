<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TenantCore | Login</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
    linear-gradient(rgba(15,23,42,0.8), rgba(15,23,42,0.8)),
    url('https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?q=80&w=1470&auto=format&fit=crop') center/cover;
    overflow:hidden;
}

/* ================= LOGIN CONTAINER ================= */

.login-container{
    width:100%;
    max-width:420px;
    padding:20px;
}

.login-card{
    background:rgba(255,255,255,0.95);
    backdrop-filter:blur(10px);
    border-radius:24px;
    padding:40px 35px;
    box-shadow:0 20px 40px rgba(0,0,0,0.25);
    animation:fadeIn 0.5s ease;
}

/* ================= LOGO ================= */

.logo{
    width:70px;
    height:70px;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    border-radius:18px;
    display:flex;
    justify-content:center;
    align-items:center;
    margin:0 auto 20px;
    color:#fff;
    font-size:30px;
    font-weight:700;
    box-shadow:0 10px 20px rgba(37,99,235,0.35);
}

/* ================= HEADINGS ================= */

h2{
    text-align:center;
    margin-bottom:8px;
    font-size:28px;
    color:#0f172a;
}

.subtitle{
    text-align:center;
    color:#64748b;
    margin-bottom:30px;
    font-size:14px;
}

/* ================= FORM ================= */

.form-group{
    margin-bottom:20px;
}

label{
    display:block;
    margin-bottom:8px;
    color:#334155;
    font-size:14px;
    font-weight:500;
}

.input-box{
    position:relative;
}

.input-box input{
    width:100%;
    padding:14px 16px;
    border:1px solid #dbeafe;
    border-radius:14px;
    background:#f8fafc;
    outline:none;
    transition:0.3s;
    font-size:14px;
}

.input-box input:focus{
    border-color:#2563eb;
    background:#fff;
    box-shadow:0 0 0 4px rgba(37,99,235,0.12);
}

/* ================= BUTTON ================= */

.login-btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:14px;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    font-size:15px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
    margin-top:10px;
}

.login-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 20px rgba(37,99,235,0.35);
}

/* ================= FOOTER ================= */

.footer{
    text-align:center;
    margin-top:22px;
    font-size:13px;
    color:#64748b;
}

/* ================= ANIMATION ================= */

@keyframes fadeIn{
    from{
        opacity:0;
        transform:translateY(20px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* ================= RESPONSIVE ================= */

@media(max-width:500px){

    .login-card{
        padding:30px 22px;
        border-radius:20px;
    }

    h2{
        font-size:24px;
    }
}

</style>
</head>

<body>

<div class="login-container">

    <div class="login-card">

        <div class="logo">
            T
        </div>

        <h2>Welcome Back</h2>
        <p class="subtitle">
            Login to access your Tenant Management System
        </p>

        <form action="login_process.php" method="POST">

            <div class="form-group">
                <label>Email Address</label>

                <div class="input-box">
                    <input type="email"
                           name="email"
                           placeholder="Enter your email"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>

                <div class="input-box">
                    <input type="password"
                           name="password"
                           placeholder="Enter your password"
                           required>
                </div>
            </div>

            <button type="submit" class="login-btn">
                Login
            </button>

        </form>

        <div class="footer">
            TenantCore Property Management System
        </div>

    </div>

</div>

</body>
</html>