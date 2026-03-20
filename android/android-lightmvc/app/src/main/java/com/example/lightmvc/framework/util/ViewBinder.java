package com.example.lightmvc.framework.util;

import android.app.Activity;
import android.view.View;

public class ViewBinder {
    public static <T extends View> T bind(Activity activity, int id) {
        return activity.findViewById(id);
    }
}
