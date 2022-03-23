# S3 File Mover

# Requirements

- php80

# Installation
```
git clone git@github.com:azakhozhy/s3-file-mover.git
cd ./s3-file-mover
```

```
composer install
```

# Usage

## Supported storages

- Selectel S3
- Digital Ocean S3
- AWS S3

```
php app.php --from-storage-driver={storage-name} --to-storage-driver={storage-name}
```
