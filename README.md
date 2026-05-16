# Vaca Compartida

**[→ Probalo en mammoli.ar/vaca](https://mammoli.ar/vaca/)**

Organizá una vaca entre amigos: cada uno suma su aporte y ven en tiempo real cuánto falta para la meta.

## Cómo funciona

1. Creás la vaca con nombre, meta y moneda
2. Compartís el link con todos los participantes
3. Cada uno agrega su aporte con su nombre y monto
4. Todos ven la barra de progreso en tiempo real

## Stack

- PHP 7.4+ · SQLite (PDO) · Vanilla JS
- Sin frameworks, sin dependencias externas
- PWA-ready (funciona desde pantalla de inicio)

## Auto-hospedaje

```bash
git clone https://github.com/camammoli/vaca.git
cp -r vaca/web/ /var/www/html/vaca/
# La BD se crea automáticamente en web/data/vaca.db
```

Requiere PHP con `pdo_sqlite`. HTTPS recomendado.

## Licencia

MIT · [Invitame un café](https://cafecito.app/mammoli) ☕
