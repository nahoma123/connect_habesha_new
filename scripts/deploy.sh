#!/bin/bash

# Exit on any error
set -e

# Configuration
APP_NAME="connect-habesha"
DEPLOY_ENV=$1
AWS_REGION="us-east-1"
ECR_REPOSITORY="${APP_NAME}"
TASK_FAMILY="${APP_NAME}-${DEPLOY_ENV}"
CLUSTER_NAME="${APP_NAME}-${DEPLOY_ENV}"
SERVICE_NAME="${APP_NAME}-service-${DEPLOY_ENV}"

# Build the Docker image
echo "Building Docker image..."
docker build -t ${APP_NAME}:latest .

# Tag and push to ECR
echo "Logging into ECR..."
aws ecr get-login-password --region ${AWS_REGION} | docker login --username AWS --password-stdin ${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com

echo "Tagging image..."
docker tag ${APP_NAME}:latest ${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPOSITORY}:latest

echo "Pushing image to ECR..."
docker push ${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPOSITORY}:latest

# Update ECS service
echo "Updating ECS service..."
aws ecs update-service \
    --cluster ${CLUSTER_NAME} \
    --service ${SERVICE_NAME} \
    --force-new-deployment \
    --region ${AWS_REGION}

echo "Waiting for service to stabilize..."
aws ecs wait services-stable \
    --cluster ${CLUSTER_NAME} \
    --services ${SERVICE_NAME} \
    --region ${AWS_REGION}

echo "Deployment completed successfully!" 