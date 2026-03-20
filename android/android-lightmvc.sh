#!/usr/bin/env bash

set -e

PROJECT_NAME="android-lightmvc"
PACKAGE="com.example.lightmvc"
PACKAGE_PATH="${PACKAGE//./\/}"

echo "Creating lightweight Android MVC project: $PROJECT_NAME"
echo "Using AGP 9.1.0 + Gradle 9.4 + compileSdk 36 (March 2026 defaults)"

mkdir -p "$PROJECT_NAME"
cd "$PROJECT_NAME"

#################################################
# GRADLE WRAPPER & SETTINGS
#################################################

mkdir -p gradle/wrapper

# settings.gradle.kts
cat > settings.gradle.kts << 'EOF'
pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }
}

dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)
    repositories {
        google()
        mavenCentral()
    }
}

rootProject.name = "LightMVC"
include(":app")
EOF

# gradle.properties
cat > gradle.properties << 'EOF'
org.gradle.jvmargs=-Xmx2048m -Dfile.encoding=UTF-8
android.useAndroidX=true
kotlin.code.style=official
android.nonTransitiveRClass=true
EOF

# Top-level build.gradle.kts
cat > build.gradle.kts << 'EOF'
plugins {
    id("com.android.application") version "9.1.0" apply false
}

tasks.register("clean", Delete::class) {
    delete(rootProject.buildDir)
}
EOF

# Gradle wrapper files (you still need to run ./gradlew wrapper after, or copy from existing install)
# For a truly offline-first script we could base64-encode them, but simplest:
echo "→ After creation run:   ./gradlew wrapper --gradle-version=9.4   to generate wrapper"

#################################################
# APP MODULE
#################################################

mkdir -p app/src/main/java/"$PACKAGE_PATH"/{framework/{controller,model,view,router,event,net,util},app/{controller,model,view}}
mkdir -p app/src/main/res/{layout,values}
mkdir -p app

# app/build.gradle.kts
cat > app/build.gradle.kts << 'EOF'
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
EOF

#################################################
# SOURCE FILES (same as original, just path-adjusted if needed)
#################################################

# BaseModel
cat > app/src/main/java/"$PACKAGE_PATH"/framework/model/BaseModel.java << 'EOF'
package com.example.lightmvc.framework.model;

public abstract class BaseModel {
    protected long created = System.currentTimeMillis();

    public long getCreated() {
        return created;
    }
}
EOF

# BaseController
cat > app/src/main/java/"$PACKAGE_PATH"/framework/controller/BaseController.java << 'EOF'
package com.example.lightmvc.framework.controller;

import com.example.lightmvc.framework.event.EventBus;

public abstract class BaseController {
    protected EventBus events = EventBus.get();
}
EOF

# BaseActivity
cat > app/src/main/java/"$PACKAGE_PATH"/framework/view/BaseActivity.java << 'EOF'
package com.example.lightmvc.framework.view;

import android.app.Activity;
import android.os.Bundle;

public abstract class BaseActivity extends Activity {
    protected abstract void bindViews();
    protected abstract void bindEvents();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        bindViews();
        bindEvents();
    }
}
EOF

# Router
cat > app/src/main/java/"$PACKAGE_PATH"/framework/router/Router.java << 'EOF'
package com.example.lightmvc.framework.router;

import android.app.Activity;
import android.content.Intent;

public class Router {
    public static void go(Activity from, Class<?> to) {
        Intent intent = new Intent(from, to);
        from.startActivity(intent);
    }
}
EOF

# EventBus
cat > app/src/main/java/"$PACKAGE_PATH"/framework/event/EventBus.java << 'EOF'
package com.example.lightmvc.framework.event;

import java.util.HashMap;
import java.util.ArrayList;

public class EventBus {

    private static EventBus instance;

    private final HashMap<String, ArrayList<EventListener>> listeners = new HashMap<>();

    public interface EventListener {
        void onEvent(Object data);
    }

    public static EventBus get() {
        if (instance == null) {
            instance = new EventBus();
        }
        return instance;
    }

    public void on(String event, EventListener listener) {
        listeners.computeIfAbsent(event, k -> new ArrayList<>()).add(listener);
    }

    public void emit(String event, Object data) {
        var list = listeners.get(event);
        if (list != null) {
            for (EventListener l : list) {
                l.onEvent(data);
            }
        }
    }
}
EOF

# JsonClient
cat > app/src/main/java/"$PACKAGE_PATH"/framework/net/JsonClient.java << 'EOF'
package com.example.lightmvc.framework.net;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class JsonClient {
    public static String get(String urlStr) {
        try {
            URL url = new URL(urlStr);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");

            BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
            String input;
            StringBuilder response = new StringBuilder();

            while ((input = in.readLine()) != null) {
                response.append(input);
            }
            in.close();

            return response.toString();
        } catch (Exception e) {
            return null;
        }
    }
}
EOF

# ViewBinder
cat > app/src/main/java/"$PACKAGE_PATH"/framework/util/ViewBinder.java << 'EOF'
package com.example.lightmvc.framework.util;

import android.app.Activity;
import android.view.View;

public class ViewBinder {
    public static <T extends View> T bind(Activity activity, int id) {
        return activity.findViewById(id);
    }
}
EOF

# Sample Model
cat > app/src/main/java/"$PACKAGE_PATH"/app/model/CounterModel.java << 'EOF'
package com.example.lightmvc.app.model;

import com.example.lightmvc.framework.model.BaseModel;

public class CounterModel extends BaseModel {
    private int count = 0;

    public void inc() { count++; }
    public void dec() { count--; }
    public int get()  { return count; }
}
EOF

# Sample Controller
cat > app/src/main/java/"$PACKAGE_PATH"/app/controller/MainController.java << 'EOF'
package com.example.lightmvc.app.controller;

import com.example.lightmvc.framework.controller.BaseController;
import com.example.lightmvc.app.model.CounterModel;

public class MainController extends BaseController {
    private final CounterModel model = new CounterModel();

    public String inc() {
        model.inc();
        return String.valueOf(model.get());
    }
}
EOF

# MainActivity
cat > app/src/main/java/"$PACKAGE_PATH"/app/view/MainActivity.java << 'EOF'
package com.example.lightmvc.app.view;

import android.widget.Button;
import android.widget.TextView;

import com.example.lightmvc.R;
import com.example.lightmvc.framework.view.BaseActivity;
import com.example.lightmvc.framework.util.ViewBinder;
import com.example.lightmvc.app.controller.MainController;

public class MainActivity extends BaseActivity {

    private MainController controller;
    private TextView counter;

    @Override
    protected void bindViews() {
        setContentView(R.layout.activity_main);

        counter = ViewBinder.bind(this, R.id.counterText);
        controller = new MainController();
    }

    @Override
    protected void bindEvents() {
        Button btn = ViewBinder.bind(this, R.id.incButton);

        btn.setOnClickListener(v ->
            counter.setText(controller.inc())
        );
    }
}
EOF

#################################################
# RESOURCES
#################################################

cat > app/src/main/res/layout/activity_main.xml << 'EOF'
<?xml version="1.0" encoding="utf-8"?>
<LinearLayout xmlns:android="http://schemas.android.com/apk/res/android"
    android:layout_width="match_parent"
    android:layout_height="match_parent"
    android:orientation="vertical"
    android:gravity="center"
    android:padding="16dp">

    <TextView
        android:id="@+id/counterText"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:text="0"
        android:textSize="40sp"
        android:layout_marginBottom="24dp"/>

    <Button
        android:id="@+id/incButton"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:text="Increment"
        android:padding="12dp"/>

</LinearLayout>
EOF

cat > app/src/main/res/values/strings.xml << 'EOF'
<resources>
    <string name="app_name">LightMVC</string>
</resources>
EOF

cat > app/src/main/res/values/styles.xml << 'EOF'
<resources>
    <style name="AppTheme" parent="android:Theme.Material.Light.NoActionBar">
    </style>
</resources>
EOF

cat > app/src/main/AndroidManifest.xml << 'EOF'
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns:android="http://schemas.android.com/apk/res/android"
    package="com.example.lightmvc">

    <application
        android:label="@string/app_name"
        android:theme="@style/AppTheme">

        <activity
            android:name=".app.view.MainActivity"
            android:exported="true">
            <intent-filter>
                <action android:name="android.intent.action.MAIN" />
                <category android:name="android.intent.category.LAUNCHER" />
            </intent-filter>
        </activity>

    </application>

</manifest>
EOF

#################################################
# README + .gitignore
#################################################

cat > README.md << 'EOF'
# Android LightMVC

Ultra-lightweight MVC experiment for Android – no libraries, pure SDK.

Created with AGP 9.1 / Gradle 9.4 / compileSdk 36 (March 2026)

## How to build

1. `./gradlew wrapper --gradle-version=9.4`   (first time only)
2. Open folder in Android Studio → let it sync
3. Run on emulator/device

Enjoy!
EOF

cat > .gitignore << 'EOF'
.gradle
build
local.properties
*.iml
.idea
EOF

#################################################

echo ""
echo "Project '$PROJECT_NAME' created successfully!"
echo ""
echo "Next steps:"
echo "  1. cd $PROJECT_NAME"
echo "  2. ./gradlew wrapper --gradle-version=9.4     # generates gradlew & wrapper files"
echo "     (or copy gradle/wrapper from another project)"
echo "  3. Open the folder in Android Studio"
echo "  4. Wait for Gradle sync → then Run"
echo ""
echo "If sync fails: check SDK 36 is installed in Android Studio SDK Manager."
echo "Enjoy your minimal MVC playground!"
