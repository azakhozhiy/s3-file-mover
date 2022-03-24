# S3 File Mover

Console application for moving files from one S3 to another S3.

## Requirements

- php80

## Installation
```
git clone git@github.com:azakhozhy/s3-file-mover.git
cd ./s3-file-mover
```

```
composer install
```

## Usage

### Predefined S3 storages

- Selectel S3 (selectel-s3)
- Digital Ocean S3 (do-s3)
- AWS S3 (aws-s3)

### Launch
```
php app.php --from-storage-driver={storage-name} --to-storage-driver={storage-name}
```
