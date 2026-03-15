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
