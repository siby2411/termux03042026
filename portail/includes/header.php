<div class="omega-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-7">
                <div class="omega-brand">
                    <div class="omega-logo">
                        <i class="fas fa-store"></i>
                        <i class="fas fa-shopping-cart"></i>
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="omega-title">
                        <h1>Oméga informatique CONSULTING</h1>
                        <h2>Gestion Portail E-Commerce</h2>
                        <div class="omega-tagline">
                            <span>🛒 Marketplace Professionnelle</span>
                            <span>📦 Gestion des Commandes</span>
                            <span>🤝 Clients & Fournisseurs</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5 text-end">
                <div class="consultant-info">
                    <i class="fas fa-user-tie"></i>
                    <div>
                        <span>Mohamed Siby</span>
                        <small>Consultant en Informatique</small>
                    </div>
                </div>
                <div class="omega-contact">
                    <i class="fas fa-phone-alt"></i> +221 77 123 45 67
                    <span class="separator">|</span>
                    <i class="fas fa-envelope"></i> contact@omega-consulting.sn
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.omega-header {
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    color: white;
    padding: 20px 0;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    border-bottom: 3px solid #ff6b6b;
}

.omega-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,107,107,0.1) 0%, transparent 70%);
    animation: pulse 8s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.3; }
    50% { transform: scale(1.5); opacity: 0.6; }
}

.omega-brand {
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    z-index: 1;
}

.omega-logo {
    display: flex;
    gap: 15px;
    font-size: 2.5rem;
    background: rgba(255,107,107,0.2);
    padding: 15px;
    border-radius: 20px;
    backdrop-filter: blur(5px);
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

.omega-logo i {
    filter: drop-shadow(0 0 10px rgba(255,107,107,0.5));
    transition: all 0.3s ease;
}

.omega-logo i:hover {
    transform: scale(1.1);
}

.omega-title h1 {
    font-size: 1.6rem;
    font-weight: 800;
    margin: 0;
    background: linear-gradient(135deg, #fff, #ff6b6b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.omega-title h2 {
    font-size: 1rem;
    font-weight: 500;
    margin: 5px 0 0;
    opacity: 0.9;
}

.omega-tagline {
    display: flex;
    gap: 15px;
    margin-top: 8px;
    font-size: 0.75rem;
    color: #ffd93d;
}

.omega-tagline span {
    background: rgba(255,255,255,0.1);
    padding: 4px 12px;
    border-radius: 20px;
}

.consultant-info {
    background: linear-gradient(135deg, #ff6b6b, #ff8c8c);
    padding: 10px 20px;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
    transition: transform 0.3s;
}

.consultant-info:hover {
    transform: translateY(-2px);
}

.consultant-info i {
    font-size: 1.5rem;
}

.consultant-info span {
    font-size: 1rem;
    font-weight: 700;
}

.consultant-info small {
    display: block;
    font-size: 0.65rem;
    opacity: 0.9;
}

.omega-contact {
    font-size: 0.75rem;
    opacity: 0.8;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
}

.separator {
    opacity: 0.5;
}

@media (max-width: 768px) {
    .omega-brand { flex-direction: column; text-align: center; }
    .omega-title h1 { font-size: 1.2rem; }
    .omega-tagline { flex-wrap: wrap; justify-content: center; }
    .consultant-info { padding: 8px 15px; }
    .omega-contact { justify-content: center; flex-wrap: wrap; }
}
</style>
