package com.example.lightmvc.framework.net;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class JsonClient {
    public static String get(String urlStr) {
        try {
            URL url = new URL(urlStr);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");

            BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
            String input;
            StringBuilder response = new StringBuilder();

            while ((input = in.readLine()) != null) {
                response.append(input);
            }
            in.close();

            return response.toString();
        } catch (Exception e) {
            return null;
        }
    }
}
