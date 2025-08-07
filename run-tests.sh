#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🧪 Vinted Clone API - Test Suite${NC}"
echo "=================================="

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}⚠️  Installing dependencies...${NC}"
    composer install
fi

# Check if .env.testing exists
if [ ! -f ".env.testing" ]; then
    echo -e "${YELLOW}⚠️  Creating .env.testing file...${NC}"
    cp .env.example .env.testing
    php artisan key:generate --env=testing
fi

# Setup test database
echo -e "${BLUE}📊 Setting up test database...${NC}"
php artisan migrate:fresh --env=testing --seed

echo ""
echo -e "${BLUE}🚀 Running Tests...${NC}"
echo ""

# Function to run a specific test suite
run_test_suite() {
    local suite_name=$1
    local test_path=$2
    
    echo -e "${YELLOW}Running ${suite_name}...${NC}"
    if php artisan test $test_path --env=testing; then
        echo -e "${GREEN}✅ ${suite_name} passed!${NC}"
        return 0
    else
        echo -e "${RED}❌ ${suite_name} failed!${NC}"
        return 1
    fi
    echo ""
}

# Track test results
failed_tests=0

# Run Configuration Tests
run_test_suite "Configuration Tests" "tests/Feature/ConfigurationTest.php"
if [ $? -ne 0 ]; then ((failed_tests++)); fi

# Run Unit Tests
echo -e "${YELLOW}Running Unit Tests...${NC}"
if php artisan test tests/Unit --env=testing; then
    echo -e "${GREEN}✅ Unit Tests passed!${NC}"
else
    echo -e "${RED}❌ Unit Tests failed!${NC}"
    ((failed_tests++))
fi
echo ""

# Run Feature Tests
echo -e "${YELLOW}Running Feature Tests...${NC}"
if php artisan test tests/Feature --env=testing; then
    echo -e "${GREEN}✅ Feature Tests passed!${NC}"
else
    echo -e "${RED}❌ Feature Tests failed!${NC}"
    ((failed_tests++))
fi
echo ""

# Run Integration Tests specifically
run_test_suite "Integration Tests" "tests/Feature/API/IntegrationTest.php"
if [ $? -ne 0 ]; then ((failed_tests++)); fi

# Generate test coverage (optional)
if command -v phpunit &> /dev/null; then
    echo -e "${BLUE}📈 Generating test coverage...${NC}"
    ./vendor/bin/phpunit --coverage-html coverage --coverage-text
    echo -e "${GREEN}Coverage report generated in 'coverage' directory${NC}"
    echo ""
fi

# Summary
echo "=================================="
if [ $failed_tests -eq 0 ]; then
    echo -e "${GREEN}🎉 All tests passed! Your Vinted Clone API is working perfectly!${NC}"
    exit 0
else
    echo -e "${RED}💥 $failed_tests test suite(s) failed. Please check the output above.${NC}"
    exit 1
fi
