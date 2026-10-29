<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Amuyong | Contact Directory</title>
    
    <!-- Google Fonts & FontAwesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/citizenhome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/citizencontactdirectory.css') }}">
@include('partials.favicon')
</head>
<body>

    @include('public.partials.header')

    <!-- Hero Section -->
    <section class="hero page-hero" style="background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 41, 59, 0.85) 100%), url('{{ asset('images/mission.jpg') }}');">
        <div class="container hero-content">
            <span class="badge">Directory & Hotlines</span>
            <h1 class="hero-title">Emergency & <br><span class="text-accent">Contact Directory</span></h1>
            <p class="hero-description">Quick access to important numbers, local authorities, health centers, and emergency response teams.</p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container main-content">
        
        <!-- Category 1: Barangay Amuyong Hotlines -->
        <section class="directory-group">
            <div class="group-header">
                <h2><i class="fa-solid fa-house-medical"></i> Barangay Amuyong Hotlines</h2>
            </div>
            <div class="hotlines-cards-grid">
                
                <div class="hotline-card featured">
                    <div class="card-icon">
                        <i class="fa-solid fa-building-user"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">Barangay Hall</span>
                        <h3 class="card-title">Main Office</h3>
                        <a href="tel:09587891234" class="card-number">
                            <i class="fa-solid fa-phone"></i> 0958-789-1234
                        </a>
                    </div>
                </div>

                <div class="hotline-card featured">
                    <div class="card-icon">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">Healthcare</span>
                        <h3 class="card-title">Barangay Health Center</h3>
                        <a href="tel:09587895678" class="card-number">
                            <i class="fa-solid fa-phone"></i> 0958-789-5678
                        </a>
                    </div>
                </div>

                <div class="hotline-card emergency">
                    <div class="card-icon">
                        <i class="fa-solid fa-truck-medical"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">Emergency</span>
                        <h3 class="card-title">Response Team</h3>
                        <a href="tel:09123456789" class="card-number">
                            <i class="fa-solid fa-phone"></i> 0912-345-6789
                        </a>
                    </div>
                </div>

                <div class="hotline-card">
                    <div class="card-icon">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">Security</span>
                        <h3 class="card-title">Barangay Tanod Executive</h3>
                        <a href="tel:09188888888" class="card-number">
                            <i class="fa-solid fa-phone"></i> 0918-888-8888
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- Category 2: Alfonso Municipal Hotlines -->
        <section class="directory-group">
            <div class="group-header">
                <h2><i class="fa-solid fa-building-flag"></i> Alfonso Municipal Hotlines</h2>
            </div>
            <div class="hotlines-cards-grid">
                
                <div class="hotline-card">
                    <div class="card-icon">
                        <i class="fa-solid fa-landmark"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">Municipal</span>
                        <h3 class="card-title">Municipal Hall</h3>
                        <a href="tel:0460000000" class="card-number">
                            <i class="fa-solid fa-phone"></i> (046) 000-0000
                        </a>
                    </div>
                </div>

                <div class="hotline-card">
                    <div class="card-icon">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">Health Office</span>
                        <h3 class="card-title">Municipal Health Office</h3>
                        <a href="tel:0460000001" class="card-number">
                            <i class="fa-solid fa-phone"></i> (046) 000-0001
                        </a>
                    </div>
                </div>

                <div class="hotline-card">
                    <div class="card-icon">
                        <i class="fa-solid fa-user-nurse"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">RHU</span>
                        <h3 class="card-title">Rural Health Unit</h3>
                        <a href="tel:0460000002" class="card-number">
                            <i class="fa-solid fa-phone"></i> (046) 000-0002
                        </a>
                    </div>
                </div>

                <div class="hotline-card emergency">
                    <div class="card-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">PNP</span>
                        <h3 class="card-title">Alfonso Police Station</h3>
                        <a href="tel:09171111111" class="card-number">
                            <i class="fa-solid fa-phone"></i> 0917-111-1111
                        </a>
                    </div>
                </div>

                <div class="hotline-card emergency">
                    <div class="card-icon">
                        <i class="fa-solid fa-fire-extinguisher"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">BFP</span>
                        <h3 class="card-title">Bureau of Fire Protection</h3>
                        <a href="tel:09172222222" class="card-number">
                            <i class="fa-solid fa-phone"></i> 0917-222-2222
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- Category 3: Cavite Provincial Hotlines -->
        <section class="directory-group">
            <div class="group-header">
                <h2><i class="fa-solid fa-map-location-dot"></i> Cavite Provincial Hotlines</h2>
            </div>
            <div class="hotlines-cards-grid">
                
                <div class="hotline-card">
                    <div class="card-icon">
                        <i class="fa-solid fa-hospital"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">Hospital</span>
                        <h3 class="card-title">Cavite Provincial Hospital</h3>
                        <a href="tel:0460001000" class="card-number">
                            <i class="fa-solid fa-phone"></i> (046) 000-1000
                        </a>
                    </div>
                </div>

                <div class="hotline-card emergency">
                    <div class="card-icon">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">PDRRMO</span>
                        <h3 class="card-title">Disaster Risk Reduction</h3>
                        <a href="tel:09991112222" class="card-number">
                            <i class="fa-solid fa-phone"></i> 0999-111-2222
                        </a>
                    </div>
                </div>

                <div class="hotline-card">
                    <div class="card-icon">
                        <i class="fa-solid fa-building-shield"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">Police</span>
                        <h3 class="card-title">Cavite Police Provincial</h3>
                        <a href="tel:09173334444" class="card-number">
                            <i class="fa-solid fa-phone"></i> 0917-333-4444
                        </a>
                    </div>
                </div>

                <div class="hotline-card emergency">
                    <div class="card-icon">
                        <i class="fa-solid fa-kit-medical"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">Rescue</span>
                        <h3 class="card-title">Cavite Rescue Unit</h3>
                        <a href="tel:09185556666" class="card-number">
                            <i class="fa-solid fa-phone"></i> 0918-555-6666
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- Category 4: National Emergency Hotlines -->
        <section class="directory-group">
            <div class="group-header">
                <h2><i class="fa-solid fa-landmark"></i> National Emergency Hotlines</h2>
            </div>
            <div class="hotlines-cards-grid">
                
                <div class="hotline-card emergency">
                    <div class="card-icon">
                        <i class="fa-solid fa-phone-flip"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">National</span>
                        <h3 class="card-title">National Emergency</h3>
                        <a href="tel:911" class="card-number">
                            <i class="fa-solid fa-phone"></i> 911
                        </a>
                    </div>
                </div>

                <div class="hotline-card emergency">
                    <div class="card-icon">
                        <i class="fa-solid fa-cross"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">Red Cross</span>
                        <h3 class="card-title">Philippine Red Cross</h3>
                        <a href="tel:143" class="card-number">
                            <i class="fa-solid fa-phone"></i> 143
                        </a>
                    </div>
                </div>

                <div class="hotline-card">
                    <div class="card-icon">
                        <i class="fa-solid fa-shield-cat"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">PNP</span>
                        <h3 class="card-title">PNP National Hotline</h3>
                        <a href="tel:117" class="card-number">
                            <i class="fa-solid fa-phone"></i> 117
                        </a>
                    </div>
                </div>

                <div class="hotline-card">
                    <div class="card-icon">
                        <i class="fa-solid fa-fire"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">BFP</span>
                        <h3 class="card-title">Fire Protection Direct</h3>
                        <a href="tel:0284260219" class="card-number">
                            <i class="fa-solid fa-phone"></i> (02) 8426-0219
                        </a>
                    </div>
                </div>

                <div class="hotline-card">
                    <div class="card-icon">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <div class="card-details">
                        <span class="card-tag">DOH</span>
                        <h3 class="card-title">Department of Health</h3>
                        <a href="tel:0286517800" class="card-number">
                            <i class="fa-solid fa-phone"></i> (02) 8651-7800
                        </a>
                    </div>
                </div>

            </div>
        </section>

    </main>

    @include('public.partials.footer')

</body>
</html>