```bash
#!/usr/bin/env bash

set -e

PROJECT_NAME="android-lightmvc"
PACKAGE="com.example.lightmvc"

echo "Creating Android LightMVC Framework..."

mkdir -p $PROJECT_NAME
cd $PROJECT_NAME

#################################################
# DIRECTORY STRUCTURE
#################################################

mkdir -p app/src/main/java/com/example/lightmvc/framework/{controller,model,view,router,event,net,util}
mkdir -p app/src/main/java/com/example/lightmvc/app/{controller,model,view}
mkdir -p app/src/main/res/layout
mkdir -p app/src/main/res/values
mkdir -p app/src/main

mkdir -p docs

#################################################
# BASE MODEL
#################################################

cat > app/src/main/java/com/example/lightmvc/framework/model/BaseModel.java << 'EOF'
package com.example.lightmvc.framework.model;

public abstract class BaseModel {

    protected long created = System.currentTimeMillis();

    public long getCreated(){
        return created;
    }

}
EOF

#################################################
# BASE CONTROLLER
#################################################

cat > app/src/main/java/com/example/lightmvc/framework/controller/BaseController.java << 'EOF'
package com.example.lightmvc.framework.controller;

import com.example.lightmvc.framework.event.EventBus;

public abstract class BaseController {

    protected EventBus events = EventBus.get();

}
EOF

#################################################
# BASE ACTIVITY
#################################################

cat > app/src/main/java/com/example/lightmvc/framework/view/BaseActivity.java << 'EOF'
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

#################################################
# ROUTER
#################################################

cat > app/src/main/java/com/example/lightmvc/framework/router/Router.java << 'EOF'
package com.example.lightmvc.framework.router;

import android.app.Activity;
import android.content.Intent;

public class Router {

    public static void go(Activity from, Class<?> to){
        Intent intent = new Intent(from, to);
        from.startActivity(intent);
    }

}
EOF

#################################################
# EVENT BUS
#################################################

cat > app/src/main/java/com/example/lightmvc/framework/event/EventBus.java << 'EOF'
package com.example.lightmvc.framework.event;

import java.util.HashMap;
import java.util.ArrayList;

public class EventBus {

    private static EventBus instance;

    private HashMap<String, ArrayList<EventListener>> listeners = new HashMap<>();

    public interface EventListener{
        void onEvent(Object data);
    }

    public static EventBus get(){

        if(instance == null)
            instance = new EventBus();

        return instance;
    }

    public void on(String event, EventListener listener){

        listeners.putIfAbsent(event, new ArrayList<>());
        listeners.get(event).add(listener);

    }

    public void emit(String event, Object data){

        if(!listeners.containsKey(event))
            return;

        for(EventListener l : listeners.get(event))
            l.onEvent(data);

    }

}
EOF

#################################################
# JSON CLIENT
#################################################

cat > app/src/main/java/com/example/lightmvc/framework/net/JsonClient.java << 'EOF'
package com.example.lightmvc.framework.net;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class JsonClient {

    public static String get(String urlStr){

        try{

            URL url = new URL(urlStr);

            HttpURLConnection conn =
                    (HttpURLConnection) url.openConnection();

            conn.setRequestMethod("GET");

            BufferedReader in =
                    new BufferedReader(
                            new InputStreamReader(
                                    conn.getInputStream()));

            String input;
            StringBuilder response = new StringBuilder();

            while((input = in.readLine()) != null)
                response.append(input);

            in.close();

            return response.toString();

        }catch(Exception e){
            return null;
        }

    }

}
EOF

#################################################
# VIEW BINDER
#################################################

cat > app/src/main/java/com/example/lightmvc/framework/util/ViewBinder.java << 'EOF'
package com.example.lightmvc.framework.util;

import android.app.Activity;
import android.view.View;

public class ViewBinder {

    public static <T extends View> T bind(Activity activity, int id){
        return activity.findViewById(id);
    }

}
EOF

#################################################
# SAMPLE MODEL
#################################################

cat > app/src/main/java/com/example/lightmvc/app/model/CounterModel.java << 'EOF'
package com.example.lightmvc.app.model;

import com.example.lightmvc.framework.model.BaseModel;

public class CounterModel extends BaseModel {

    private int count = 0;

    public void inc(){
        count++;
    }

    public void dec(){
        count--;
    }

    public int get(){
        return count;
    }

}
EOF

#################################################
# SAMPLE CONTROLLER
#################################################

cat > app/src/main/java/com/example/lightmvc/app/controller/MainController.java << 'EOF'
package com.example.lightmvc.app.controller;

import com.example.lightmvc.framework.controller.BaseController;
import com.example.lightmvc.app.model.CounterModel;

public class MainController extends BaseController {

    private CounterModel model = new CounterModel();

    public String inc(){

        model.inc();
        return String.valueOf(model.get());

    }

}
EOF

#################################################
# MAIN ACTIVITY
#################################################

cat > app/src/main/java/com/example/lightmvc/app/view/MainActivity.java << 'EOF'
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
    protected void bindViews(){

        setContentView(R.layout.activity_main);

        counter = ViewBinder.bind(this, R.id.counterText);

        controller = new MainController();

    }

    @Override
    protected void bindEvents(){

        Button btn = ViewBinder.bind(this, R.id.incButton);

        btn.setOnClickListener(v ->
                counter.setText(controller.inc()));

    }

}
EOF

#################################################
# LAYOUT
#################################################

cat > app/src/main/res/layout/activity_main.xml << 'EOF'
<LinearLayout
xmlns:android="http://schemas.android.com/apk/res/android"
android:orientation="vertical"
android:gravity="center"
android:padding="16dp">

<TextView
    android:id="@+id/counterText"
    android:text="0"
    android:textSize="40sp"
    android:layout_marginBottom="24dp"/>

<Button
    android:id="@+id/incButton"
    android:text="Increment"
    android:padding="12dp"/>

</LinearLayout>
EOF

#################################################
# STRINGS
#################################################

cat > app/src/main/res/values/strings.xml << 'EOF'
<resources>
<string name="app_name">LightMVC</string>
</resources>
EOF

#################################################
# STYLES
#################################################

cat > app/src/main/res/values/styles.xml << 'EOF'
<resources>

<style name="AppTheme" parent="android:Theme.Material.Light.NoActionBar">
</style>

<style name="BtnPrimary">
<item name="android:background">#0d6efd</item>
<item name="android:textColor">#ffffff</item>
</style>

</resources>
EOF

#################################################
# MANIFEST
#################################################

cat > app/src/main/AndroidManifest.xml << 'EOF'
<manifest xmlns:android="http://schemas.android.com/apk/res/android"
package="com.example.lightmvc">

<application
android:label="LightMVC"
android:theme="@style/AppTheme">

<activity android:name="com.example.lightmvc.app.view.MainActivity">

<intent-filter>
<action android:name="android.intent.action.MAIN"/>
<category android:name="android.intent.category.LAUNCHER"/>
</intent-filter>

</activity>

</application>

</manifest>
EOF

#################################################
# README
#################################################

cat > README.md << 'EOF'
# Android LightMVC

Lightweight Android MVC framework.

Features

- MVC architecture
- Event Bus
- Router
- JSON client
- No external libraries
- Minimal code base

EOF

#################################################
# ROADMAP
#################################################

cat > docs/ROADMAP.md << 'EOF'
0.1

MVC core
Router
EventBus

0.2

WebSocket
Camera streaming
Storage API

0.3

Plugin system
EOF

#################################################

echo "Project created successfully."
echo "Open the project in Android Studio or build using Gradle."
```
