package com.example.lightmvc.framework.router;

import android.app.Activity;
import android.content.Intent;

public class Router {
    public static void go(Activity from, Class<?> to) {
        Intent intent = new Intent(from, to);
        from.startActivity(intent);
    }
}
