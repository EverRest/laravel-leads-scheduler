FROM node:18-alpine

# Install dependencies
RUN apk --no-cache add \
    gnupg \
    wget \
    chromium \
    nss \
    freetype \
    freetype-dev \
    harfbuzz \
    ca-certificates \
    ttf-freefont \
    nodejs \
    npm

# Set environment variables
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD true
ENV CHROME_BIN=/usr/bin/chromium-browser

# Set working directory
WORKDIR /app

# Copy package.json and install npm packages
COPY package.json /app
RUN npm install

# Copy the rest of the application files
COPY . /app

# Expose ports
EXPOSE 3000
