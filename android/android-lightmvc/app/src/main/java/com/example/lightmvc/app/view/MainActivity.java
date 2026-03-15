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
