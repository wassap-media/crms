terraform {
  required_version = ">= 1.0"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
  
  backend "s3" {
    bucket = "crm-terraform-state"
    key    = "crm-infrastructure/terraform.tfstate"
    region = "us-east-1"
  }
}

# Configure AWS Provider
provider "aws" {
  region = var.aws_region
  
  default_tags {
    tags = {
      Project     = "CRM-System"
      Environment = var.environment
      ManagedBy   = "Terraform"
    }
  }
}

# VPC and Networking
module "vpc" {
  source = "./modules/vpc"
  
  environment    = var.environment
  vpc_cidr      = var.vpc_cidr
  azs           = var.availability_zones
  private_subnets = var.private_subnets
  public_subnets  = var.public_subnets
}

# RDS Database
module "rds" {
  source = "./modules/rds"
  
  environment           = var.environment
  vpc_id               = module.vpc.vpc_id
  private_subnet_ids   = module.vpc.private_subnet_ids
  db_name              = var.db_name
  db_username          = var.db_username
  db_password          = var.db_password
  db_instance_class    = var.db_instance_class
  db_allocated_storage = var.db_allocated_storage
  security_group_ids   = [module.vpc.default_security_group_id]
}

# Application Load Balancer
module "alb" {
  source = "./modules/alb"
  
  environment        = var.environment
  vpc_id            = module.vpc.vpc_id
  public_subnet_ids = module.vpc.public_subnet_ids
  security_group_ids = [module.vpc.alb_security_group_id]
}

# ECS Cluster and Services
module "ecs" {
  source = "./modules/ecs"
  
  environment        = var.environment
  vpc_id            = module.vpc.vpc_id
  private_subnet_ids = module.vpc.private_subnet_ids
  alb_target_group_arn = module.alb.target_group_arn
  security_group_ids = [module.vpc.ecs_security_group_id]
  db_endpoint       = module.rds.db_endpoint
  db_name           = var.db_name
  db_username       = var.db_username
  db_password       = var.db_password
}

# S3 Bucket for file storage
module "s3" {
  source = "./modules/s3"
  
  environment = var.environment
  bucket_name = var.s3_bucket_name
}

# CloudWatch Logs
module "cloudwatch" {
  source = "./modules/cloudwatch"
  
  environment = var.environment
  log_group_name = "/ecs/crm-application"
}

# Route53 DNS (if domain is provided)
module "route53" {
  source = "./modules/route53"
  count  = var.domain_name != "" ? 1 : 0
  
  environment = var.environment
  domain_name = var.domain_name
  alb_dns_name = module.alb.alb_dns_name
  alb_zone_id = module.alb.alb_zone_id
}

# ACM Certificate (if domain is provided)
module "acm" {
  source = "./modules/acm"
  count  = var.domain_name != "" ? 1 : 0
  
  domain_name = var.domain_name
  environment = var.environment
}

# ElastiCache Redis for session storage
module "elasticache" {
  source = "./modules/elasticache"
  
  environment        = var.environment
  vpc_id            = module.vpc.vpc_id
  private_subnet_ids = module.vpc.private_subnet_ids
  security_group_ids = [module.vpc.elasticache_security_group_id]
}

# CloudFront Distribution for static assets
module "cloudfront" {
  source = "./modules/cloudfront"
  
  environment = var.environment
  s3_bucket_domain_name = module.s3.bucket_domain_name
  domain_name = var.domain_name
  acm_certificate_arn = var.domain_name != "" ? module.acm[0].certificate_arn : null
}
