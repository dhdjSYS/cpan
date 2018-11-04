/*
 ============================================================================
 Name        : client.c
 Author      : xMing
 Version     : 1.0.0_alpha
 Description : A file sender client for cpan
 ============================================================================
 */
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/socket.h>
#include <errno.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <netdb.h>
#include <string.h>
#include <math.h>
#define check_status(sock,code) \
  time = 0; \
  memset(output, 0, strlen(output)*sizeof(char)); \
  while(1){ \
    time++; \
    if(time > 10){ \
      printf("连接超时,状态码"); \
      puts(code); \
      return 1; \
    } \
    if(read(sock,output,4) > 0){ \
      if(strcmp(output,code) == 0){ \
        break; \
      } \
    } \
    usleep(pow(10,6)/2); \
  }
int main(int argc,char** argv)
{
  char* password;
  if(argc < 2){
    printf("使用方法: %s [文件名] [密码](默认为0)\n",argv[0]);
    return 1;
  }else if(argc == 2){
    password = "0";
  }else{
    password = argv[2];
  }
  if(access(argv[1],0) == -1){
    printf("%s文件不存在\n",argv[1]);
    return 1;
  }
  //套接字
  int sock;
  //连接服务器时用
  struct sockaddr_in addr_in;
  //创建套接字
  if((sock = socket(AF_INET,SOCK_STREAM,IPPROTO_TCP)) < 0){
    printf("创建套接字失败: %s(errno: %d)\n", strerror(errno),errno);
    return 1;
  }
  addr_in.sin_family = AF_INET; /* 主机字节序 */
  //设置目标服务器端口
  addr_in.sin_port = htons(2333); /*short, 网络字节序 */
  //设置目标服务器
  struct hostent *hp;
  //支持域名和ip的方法
  hp = gethostbyname("35.237.187.165");
  memcpy(&addr_in.sin_addr.s_addr, hp->h_addr_list[0], (size_t)hp->h_length);
  //连接服务器
  if(connect(sock,(struct sockaddr*) &addr_in,sizeof(addr_in)) < 0){
    printf("连接失败: %s(errno: %d)\n",strerror(errno),errno);
    return 1;
  }
  int time = 0;
  char output[16];
  check_status(sock,"0000");
  FILE *fp=fopen(argv[1],"rb");
  if(fp==NULL){
	    puts("文件读取失败\n");
    	return 1;
  }
  fseek(fp, 0, SEEK_END);
  unsigned int fileLen = ftell(fp);
  char json[100];
  char *text = (char *) malloc(sizeof(char) * (fileLen+1));
  fseek(fp, 0, SEEK_SET);
  fread(text, sizeof(char), fileLen, fp);
  fclose(fp);
  sprintf(json,"{\"name\":\"%s\",\"length\":\"3\",\"password\":\"%s\"}",argv[1],/*fileLen,*/password);
  write(sock,json,strlen(json));
  check_status(sock,"0001");
  write(sock,text,strlen(text));
  free(text);
  check_status(sock,"0002");
  puts("传输完毕");
  close(sock);
  return 0;
}