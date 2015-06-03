def database_config
  @database_config ||= begin
    root = File.expand_path('../../..', __FILE__)
    
    config = File.read(File.expand_path('config/settings.inc.php', root)).split("\n").collect do |line|
      line = line.strip
      if line.match(/^define\(['"]([^'"]+)['"], +?['"]([^'"]+)['"]\)/)
        [Regexp.last_match[1], Regexp.last_match[2]]
      end
    end
    config = Hash[config.compact]
    
    {
      :database => config['DB_NAME'],
      :adapter  => 'mysql2',
      :host     => config['DB_HOST'].split(':')[0],
      :port     => config['DB_HOST'].split(':')[1],
      :username => config['DB_USER'],
      :password => config['DB_PASSWORD']
    }
  end
end

def mysql_command
  @mysql_command ||= begin
    database_config
    
    mysql_command_options = ["-u #{database_config[:username]}", "-p#{database_config[:password]}", "-h #{database_config[:host]}"]
    mysql_command_options.push "-P #{database_config[:port]}" if database_config[:port]
    mysql_command_options.push database_config[:database]
    
    mysql_command_options
  end
end