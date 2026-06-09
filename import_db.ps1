$ErrorActionPreference = 'Stop'

Write-Host "Dumping local database..."
C:\xampp\mysql\bin\mysqldump.exe -u root pawhealth_db --result-file=local_dump.sql

Write-Host "Creating Cloud Storage bucket..."
gcloud storage buckets create gs://pawhealth-6db18-sql-backups --location=us-central1

Write-Host "Uploading dump to bucket..."
gcloud storage cp local_dump.sql gs://pawhealth-6db18-sql-backups/local_dump.sql

Write-Host "Getting Cloud SQL service account..."
$sa = gcloud sql instances describe pawhealth-db --format="value(serviceAccountEmailAddress)"
Write-Host "Service Account: $sa"

Write-Host "Granting permissions to Cloud SQL service account..."
gcloud storage buckets add-iam-policy-binding gs://pawhealth-6db18-sql-backups --member="serviceAccount:$sa" --role=roles/storage.objectAdmin

Write-Host "Importing database into Cloud SQL..."
gcloud sql import sql pawhealth-db gs://pawhealth-6db18-sql-backups/local_dump.sql --database=pawhealth_db -q

Write-Host "Cleaning up bucket..."
gcloud storage rm -r gs://pawhealth-6db18-sql-backups

Write-Host "Database import complete!"
