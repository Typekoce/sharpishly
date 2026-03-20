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
