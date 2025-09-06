// Código adicional para interfaz con base de datos (opcional)
// Este código puede ser usado en un microcontrolador con WiFi como ESP32

#ifdef ESP32
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid = "nigger";
const char* password = "1234567890";
const char* serverURL = "http://your-server.com/api/check-weight";

void setupWiFi() {
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("WiFi connected");
}

bool checkWeightWithDatabase(String scaleId, float measuredWeight) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverURL);
    http.addHeader("Content-Type", "application/json");
    
    // Crear JSON payload
    StaticJsonDocument<200> doc;
    doc["scale_id"] = scaleId;
    doc["measured_weight"] = measuredWeight;
    
    String jsonString;
    serializeJson(doc, jsonString);
    
    int httpResponseCode = http.POST(jsonString);
    
    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("Database response: " + response);
      
      // Parse response
      StaticJsonDocument<300> responseDoc;
      deserializeJson(responseDoc, response);
      
      bool isWithinTolerance = responseDoc["within_tolerance"];
      return isWithinTolerance;
    }
    
    http.end();
  }
  return false;
}
#endif
