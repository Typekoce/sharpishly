plugins {
    id("com.android.application")
}

android {
    namespace = "com.example.lightmvc"
    compileSdk = 36

    defaultConfig {
        applicationId = "com.example.lightmvc"
        minSdk = 26
        targetSdk = 35
        versionCode = 1
        versionName = "1.0"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"
    }

    buildTypes {
        release {
            isMinifyEnabled = false
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    // No Kotlin plugin needed → AGP 9.0+ has built-in Kotlin support
}

dependencies {
    // Intentionally empty → pure Android SDK
}
