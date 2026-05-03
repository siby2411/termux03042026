<?php
// Bannière design pour Cabinet Radiologie
?>
<div class="omega-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="omega-brand">
                    <div class="omega-logo">
                        <i class="fas fa-chart-line"></i>
                        <i class="fas fa-microscope"></i>
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="omega-title">
                        <h1>Oméga informatique CONSULTING</h1>
                        <h2>Application de Gestion - Cabinet Radiologie</h2>
                        <div class="omega-tagline">
                            <span>🏥 Excellence en Imagerie Médicale</span>
                            <span>⚡ Solutions Innovantes</span>
                            <span>🤝 Service Premium</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="consultant-info">
                    <i class="fas fa-user-tie"></i>
                    <span>Mohamed Siby</span>
                    <small>Consultant en Informatique</small>
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
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    color: white;
    padding: 20px 0;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    border-bottom: 3px solid #e94560;
}

.omega-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(233,69,96,0.1) 0%, transparent 70%);
    animation: pulse 8s ease-in-out infinite;
}

.omega-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, #e94560, #ff6b6b, #e94560, transparent);
    animation: slide 3s linear infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.3; }
    50% { transform: scale(1.5); opacity: 0.6; }
}

@keyframes slide {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
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
    background: rgba(233,69,96,0.2);
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
    filter: drop-shadow(0 0 10px rgba(233,69,96,0.5));
    transition: all 0.3s ease;
}

.omega-logo i:hover {
    transform: scale(1.1);
    filter: drop-shadow(0 0 20px #e94560);
}

.omega-title h1 {
    font-size: 1.8rem;
    font-weight: 800;
    margin: 0;
    background: linear-gradient(135deg, #fff 0%, #e94560 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 1px;
}

.omega-title h2 {
    font-size: 1.1rem;
    font-weight: 500;
    margin: 5px 0 0 0;
    opacity: 0.9;
    color: #a8b3cf;
}

.omega-tagline {
    display: flex;
    gap: 20px;
    margin-top: 10px;
    font-size: 0.8rem;
    color: #ffd93d;
}

.omega-tagline span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.1);
    padding: 4px 12px;
    border-radius: 20px;
    backdrop-filter: blur(5px);
}

.consultant-info {
    background: linear-gradient(135deg, #e94560, #ff6b6b);
    padding: 12px 20px;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
    box-shadow: 0 4px 15px rgba(233,69,96,0.3);
    transition: transform 0.3s ease;
}

.consultant-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(233,69,96,0.4);
}

.consultant-info i {
    font-size: 1.5rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.consultant-info span {
    font-size: 1.1rem;
    font-weight: 700;
}

.consultant-info small {
    display: block;
    font-size: 0.7rem;
    opacity: 0.9;
    font-weight: normal;
}

.omega-contact {
    font-size: 0.8rem;
    opacity: 0.8;
    margin-top: 8px;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
}

.omega-contact i {
    margin-right: 5px;
}

.separator {
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .omega-brand {
        flex-direction: column;
        text-align: center;
        margin-bottom: 15px;
    }
    
    .omega-title h1 {
        font-size: 1.3rem;
    }
    
    .omega-title h2 {
        font-size: 0.9rem;
    }
    
    .omega-tagline {
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
    }
    
    .omega-tagline span {
        font-size: 0.7rem;
    }
    
    .consultant-info {
        padding: 8px 15px;
    }
    
    .consultant-info span {
        font-size: 0.9rem;
    }
    
    .omega-contact {
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
    }
}
</style>
