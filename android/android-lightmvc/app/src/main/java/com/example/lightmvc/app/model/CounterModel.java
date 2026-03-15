package com.example.lightmvc.app.model;

import com.example.lightmvc.framework.model.BaseModel;

public class CounterModel extends BaseModel {
    private int count = 0;

    public void inc() { count++; }
    public void dec() { count--; }
    public int get()  { return count; }
}
