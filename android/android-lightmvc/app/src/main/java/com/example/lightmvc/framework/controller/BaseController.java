package com.example.lightmvc.framework.controller;

import com.example.lightmvc.framework.event.EventBus;

public abstract class BaseController {
    protected EventBus events = EventBus.get();
}
