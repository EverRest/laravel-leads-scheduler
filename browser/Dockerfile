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
    udev \
    nodejs \
    npm

# Set environment variables
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD true
ENV CHROME_BIN=/usr/bin/chromium
ENV PUPPETEER_EXECUTABLE_PATH /usr/bin/chromium-browser

# Set working directory
WORKDIR /app

# Copy package.json and install npm packages
COPY package*.json /app/
RUN npm install

# Copy the rest of the application files
COPY . .

# Expose ports
EXPOSE 4000

CMD ["npm", "run", "start"]
