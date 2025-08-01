# 🏡 Real Estate Laravel API

Ovo je RESTful API za aplikaciju za upravljanje nekretninama, napravljena u Laravelu. Omogućava korisnicima da pretražuju, kreiraju i upravljaju nekretninama, šalju upite, registruju se i pristupaju kao korisnici ili administratori.

---

## 🔧 Instalacija

### 1. Kloniranje repozitorijuma

```bash
git clone https://github.com/elab-development/SeminarskiSTEHNekretnine
cd realestate
```

```bash
composer install
php artisan serve
```

## 🚀 Slučajevi korišćenja

### 🧑‍💼 Svi korisnici

✅ Pregled i pretraga nekretnina

✅ Pregled tipova nekretnina

### 🧑‍💼 Korisnici (Users)

✅ Registracija

✅ Prijava i dobijanje tokena

✅ Slanje upita za nekretninu

✅ Pregled sopstvenih upita

### 👨‍💼 Administratori (Admins)

✅ Upravljanje nekretninama (CRUD)

✅ Upravljanje tipovima nekretnina (CRUD)

✅ Pregled svih korisničkih upita

✅ Pregled svih korisnika

✅ Dohvatanje upita određenog korisnika
