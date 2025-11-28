//Incluye las librerías
#include "HX711.h"
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <EEPROM.h>

LiquidCrystal_I2C lcd(0x3F, 16, 2);
HX711 balanza;

const int zero = 2;
int DT = 4;
int CLK = 5;
int peso_calibracion = 42
; // Peso de calibración
long escala;
int state_zero = 0;
int last_state_zero = 0;

//Función calibración
void calibration() {
  boolean conf = true;
  long adc_lecture;
  lcd.setCursor(0, 0);
  lcd.print("Calibrando base");
  lcd.setCursor(4, 1);
  lcd.print("Balanza");
  delay(3000);
  balanza.read();
  balanza.set_scale();
  balanza.tare(20);
  lcd.clear();

  while (conf == true) {
    lcd.setCursor(1, 0);
    lcd.print("Peso referencial:");
    lcd.setCursor(1, 1);
    lcd.print(peso_calibracion);
    lcd.print(" g        ");
    delay(3000);
    lcd.clear();
    lcd.setCursor(1, 0);
    lcd.print("Ponga el Peso");
    lcd.setCursor(1, 1);
    lcd.print("Referencial");
    delay(3000);
    adc_lecture = balanza.get_value(100);
    escala = adc_lecture / peso_calibracion;
    EEPROM.put(0, escala);
    delay(100);
    lcd.setCursor(1, 0);
    lcd.print("Retire el Peso");
    lcd.setCursor(1, 1);
    lcd.print("referencial");
    delay(3000);
    lcd.clear();
    lcd.setCursor(1, 0);
    lcd.print("READY!!....");
    delay(3000);
    lcd.clear();
    conf = false;
  }
}

void setup() {
  Serial.begin(9600);

  balanza.begin(DT, CLK);
  pinMode(zero, INPUT_PULLUP);   // mejor con pull-up interno
  pinMode(13, OUTPUT);

   Wire.begin();        // Inicializa bus I2C
  scanI2C();           // Escanea y muestra direcciones

  lcd.init();          // Prepara la librería LCD
  lcd.begin(16, 2);    // Tamaño: 16x2
  lcd.backlight();     // Enciende backlight

  EEPROM.get(0, escala);
  if (escala == 0) escala = 2280;
  balanza.set_scale(escala);
  balanza.tare(20);
}

// Escanea el bus I2C y muestra direcciones en Serial y en LCD
void scanI2C() {
  Serial.println("\nEscaneando bus I2C...");
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Escaneando I2C");
  delay(1000);

  bool found = false;
  for (uint8_t addr = 1; addr < 127; addr++) {
    Wire.beginTransmission(addr);
    if (Wire.endTransmission() == 0) {
      // Dispositivo respondiente
      Serial.print(" > I2C device at 0x");
      if (addr < 16) Serial.print('0');
      Serial.println(addr, HEX);

      // Muestra brevemente en LCD
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("I2C @ 0x");
      if (addr < 16) lcd.print('0');
      lcd.print(addr, HEX);
      delay(1500);

      found = true;
    }
  }
  if (!found) {
    Serial.println("No se encontraron dispositivos I2C.");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("No I2C devices");
    delay(1500);
  }
  lcd.clear();
}

void loop() {
  float peso = balanza.get_units(10);

  // Limpia y escribe en la primera línea
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Peso: ");
  lcd.print(peso, 0);
  lcd.print(" g");

  // Serial para depuración
  Serial.print("Peso: ");
  Serial.print(peso, 2);
  Serial.println(" g");

  // Función “zero”
  int state_zero = digitalRead(zero);
  if (state_zero == LOW && last_state_zero == HIGH) {
    balanza.tare(10);
  }
  last_state_zero = state_zero;

  // LED indicador
  digitalWrite(13, (peso >= 20) ? HIGH : LOW);

  delay(500);
}
