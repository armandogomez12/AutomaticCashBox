#include "HX711.h"
#include <WiFi.h>
#include <HTTPClient.h>
#include <EEPROM.h>

// ===== CONFIGURACIÓN HARDWARE =====
const int DT = 4;    // Pin DT HX711
const int CLK = 5;   // Pin CLK HX711

HX711 balanza;
float escala = 2280.0;

// ===== CONFIGURACIÓN WiFi =====
const char* ssid = "TU_SSID_AQUI";
const char* password = "TU_CONTRASEÑA_AQUI";
const char* serverUrl = "http://192.168.x.x/public/check-weight.php";

float weight = 0;

// ===== SETUP =====
void setup() {
  Serial.begin(115200);
  EEPROM.begin(512);
  
  // Inicializar balanza
  balanza.begin(DT, CLK);

  // Cargar escala desde EEPROM
  EEPROM.get(0, escala);
  if (escala == 0) escala = 2280;
  balanza.set_scale(escala);
  balanza.tare(20);

  // Conectar a WiFi
  connectToWiFi();
}

// ===== FUNCIONES =====

void connectToWiFi() {
  Serial.println("\nConectando a WiFi...");
  
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✓ Conectado a WiFi");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n✗ Error conectando a WiFi");
  }
}

float readWeightFromScale() {
  if (balanza.is_ready()) {
    return balanza.get_units(10); // Promedio de 10 lecturas
  }
  return 0;
}

void sendWeightToServer(float weight) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi no conectado");
    return;
  }

  HTTPClient http;
  
  // JSON con los datos a enviar
  String jsonPayload = "{\"scale_id\":\"XBOX_SERIES\",\"weight\":" + String(weight, 2) + "}";
  
  Serial.println("Enviando: " + jsonPayload);
  
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");
  
  int httpCode = http.POST(jsonPayload);
  
  if (httpCode > 0) {
    String response = http.getString();
    Serial.println("Respuesta: " + response);
  } else {
    Serial.println("Error en la petición");
  }
  http.end();
}

// ===== LOOP =====
void loop() {
  // Reconectar WiFi si se desconecta
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi desconectado, reconectando...");
    connectToWiFi();
  }
  
  // Leer peso de la báscula
  weight = readWeightFromScale();
  
  Serial.print("Peso: ");
  Serial.print(weight);
  Serial.println(" g");
  
  // Enviar peso al servidor
  sendWeightToServer(weight);
  
  delay(4000); // Esperar 4 segundos antes del siguiente envío
}