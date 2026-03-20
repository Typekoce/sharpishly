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
