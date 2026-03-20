package com.example.lightmvc.framework.model;

public abstract class BaseModel {
    protected long created = System.currentTimeMillis();

    public long getCreated() {
        return created;
    }
}
